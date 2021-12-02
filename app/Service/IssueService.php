<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Service;

use App\Constants\ErrorCode;
use App\Constants\Permission;
use App\Constants\Schema;
use App\Constants\StatusConstant;
use App\Event\IssueEvent;
use App\Exception\BusinessException;
use App\Model\Issue;
use App\Model\Label;
use App\Model\Project;
use App\Model\User;
use App\Project\Eloquent\Labels;
use App\Project\Provider;
use App\Service\Dao\IssueDao;
use App\Service\Dao\LabelDao;
use App\Service\Dao\ModuleDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\IssueFormatter;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Illuminate\Support\Facades\Event;
use Psr\EventDispatcher\EventDispatcherInterface;

class IssueService extends Service
{
    use TimeTrackTrait;

    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected IssueFormatter $formatter;

    public function requiredCheck(array $schema, array $data, string $mode = 'create'): bool
    {
        foreach ($schema as $field) {
            if (isset($field['required']) && $field['required']) {
                if ($mode == 'update') {
                    if (isset($data[$field['key']]) && ! $data[$field['key']] && $data[$field['key']] !== 0) {
                        return false;
                    }
                } else {
                    if (! isset($data[$field['key']]) || ! $data[$field['key']] && $data[$field['key']] !== 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $input = [
     *     'type' => '',
     * ]
     */
    public function store(array $input, User $user, Project $project)
    {
        $type = $input['type'];
        $assigneeId = $input['assignee'] ?? null;
        $moduleIds = $input['module'] ?? null;
        $resolution = $input['resolution'] ?? null;

        $schema = $this->provider->getSchemaByType($type);
        if (! $schema) {
            throw new BusinessException(ErrorCode::ISSUE_TYPE_SCHEMA_NOT_EXIST);
        }

        if (! $this->requiredCheck($schema, $input)) {
            throw new BusinessException(ErrorCode::ISSUE_TYPE_SCHEMA_REQUIRED);
        }

        $insValues = [];
        foreach ($schema as $field) {
            $fieldValue = $input[$field['key']] ?? null;
            if (empty($fieldValue)) {
                continue;
            }

            if ($field['type'] == Schema::FIELD_TIME_TRACKING) {
                if (! $this->ttCheck($fieldValue)) {
                    throw new BusinessException(ErrorCode::ISSUE_TIME_TRACKING_INVALID);
                }
                $insValues[$field['key']] = $this->ttHandle($fieldValue);
                $insValues[$field['key'] . '_m'] = $this->ttHandleInM($insValues[$field['key']]);
            } elseif ($field['type'] == Schema::FIELD_DATE_PICKER || $field['type'] == Schema::FIELD_DATE_TIME_PICKER) {
                if ($this->isTimestamp($fieldValue) === false) {
                    throw new BusinessException(ErrorCode::ISSUE_DATE_TIME_PICKER_INVALID);
                }
            } elseif ($field['type'] == Schema::FIELD_SINGLE_USER) {
                $userInfo = di()->get(UserDao::class)->first((int) $fieldValue);
                if ($userInfo) {
                    $insValues[$field['key']] = di()->get(UserFormatter::class)->small($user);
                }
            } elseif ($field['type'] == Schema::FIELD_MULTI_USER) {
                $users = di()->get(UserDao::class)->findMany($fieldValue);
                $userIds = [];
                foreach ($users as $user) {
                    $insValues[$field['key']][] = di()->get(UserFormatter::class)->small($user);
                    $userIds[] = $user->id;
                }
                $insValues[$field['key'] . '_ids'] = $userIds;
            }
        }

        // handle assignee
        $assignee = [];
        if (! $assigneeId) {
            if ($moduleIds) {
                $module = di()->get(ModuleDao::class)->first((int) $moduleIds[0]);
                if ($module?->default_assignee === 'modulePrincipal') {
                    $assigneeId = $module->principal['id'] ?? '';
                } elseif ($module?->default_assignee === 'projectPrincipal') {
                    $assigneeId = $this->provider->getProjectPrincipal($project->key)['id'] ?? '';
                }
            }
        }

        if ($assigneeId) {
            if ($assigneeId !== $user->id && ! di()->get(AclService::class)->hasAccess($assigneeId, $project, Permission::ASSIGNED_ISSUE)) {
                throw new BusinessException(ErrorCode::ASSIGNED_USER_PERMISSION_DENIED);
            }

            $userInfo = di()->get(UserDao::class)->first($assigneeId);
            if ($userInfo) {
                $assignee = di()->get(UserFormatter::class)->small($userInfo);
            }
        }

        if (! $assignee) {
            $assignee = di()->get(UserFormatter::class)->small($user);
        }

        $maxNumber = di()->get(IssueDao::class)->count($project->key) + 1;

        // TODO: Support Workflow
        // $workflow = $this->initializeWorkflow($issue_type);
        // $insValues = $insValues + $workflow;

        $insValues = $insValues + Arr::only($input, $this->getValidKeysBySchema($schema));

        Db::beginTransaction();
        try {
            $model = new Issue();
            $model->project_key = $project->key;
            $model->type = $type;
            $model->del_flg = StatusConstant::NOT_DELETED;
            $model->resolution = $resolution ?: StatusConstant::STATUS_UNRESOLVED;
            $model->assignee = $assignee;
            $model->reporter = di()->get(UserFormatter::class)->small($user);
            $model->no = $maxNumber;
            $model->data = $insValues;
            $model->save();

            // create the Labels for project
            if (isset($insValues['labels']) && $insValues['labels']) {
                $this->createLabels($project->key, $insValues['labels']);
            }

            // TODO: Support History
            // Provider::snap2His($project_key, $id, $schema);

            // TODO: IssueEvent 通知 Activity 和 Webhook
            // Event::fire(new IssueEvent($project_key, $id->__toString(), $insValues['reporter'], ['event_key' => 'create_issue']));
            di()->get(EventDispatcherInterface::class)->dispatch(new IssueEvent($model));

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->show($model);
    }

    public function show(Issue $issue): array
    {
        $schema = $this->provider->getSchemaByType($issue->type);
        if (! $schema) {
            throw new BusinessException(ErrorCode::ISSUE_TYPE_SCHEMA_NOT_EXIST);
        }

        $result = di()->get(IssueFormatter::class)->base($issue);
        $result['assignee']['avatar'] = $issue->assigneeModel?->avatar ?? '';

        foreach ($schema as $field) {
            if ($field['type'] === Schema::FIELD_FILE && ! empty($result[$field['key']])) {
                foreach ($result[$field['key']] as $key => $fid);
                // TODO: 处理文件
                // $result[$field['key']][$key] = File::find($fid);
            }
        }

        // TODO: 获取可用的 workflow
        // if (isset($issue['entry_id']) && $issue['entry_id'] && $this->isPermissionAllowed($project_key, 'exec_workflow')) {
        //     try {
        //         $wf = new Workflow($issue['entry_id']);
        //         $issue['wfactions'] = $wf->getAvailableActions(['project_key' => $project_key, 'issue_id' => $id, 'caller' => $this->user->id]);
        //     } catch (Exception $e) {
        //         $issue['wfactions'] = [];
        //     }
        //
        //     foreach ($issue['wfactions'] as $key => $action) {
        //         if (isset($action['screen']) && $action['screen'] && $action['screen'] != 'comments') {
        //             $issue['wfactions'][$key]['schema'] = Provider::getSchemaByScreenId($project_key, $issue['type'], $action['screen']);
        //         }
        //     }
        // }

        if ($issue->parent_id > 0) {
            $result['parent'] = $this->formatter->base($issue->parent);
        } else {
            $result['hasSubtasks'] = $issue->children->isNotEmpty();
        }

        $result['subtasks'] = $this->formatter->formatList($issue->children);

        // TODO: 后期再做
        // $issue['links'] = $this->getLinks($project_key, $issue);

        // $issue['watchers'] = array_column(Watch::where('issue_id', $id)->orderBy('_id', 'desc')->get()->toArray(), 'user');
        // foreach ($issue['watchers'] as $key => $watch) {
        //     $user = EloquentUser::find($watch['id']);
        //     if (isset($user->avatar) && $user->avatar) {
        //         $issue['watchers'][$key]['avatar'] = $user->avatar;
        //     }
        // }
        //
        // if (Watch::where('issue_id', $id)->where('user.id', $this->user->id)->exists()) {
        //     $issue['watching'] = true;
        // }
        //
        // $comments_num = 0;
        // $comments = DB::collection('comments_' . $project_key)
        //     ->where('issue_id', $id)
        //     ->get();
        // foreach ($comments as $comment) {
        //     ++$comments_num;
        //     if (isset($comment['reply'])) {
        //         $comments_num += count($comment['reply']);
        //     }
        // }
        // $issue['comments_num'] = $comments_num;
        //
        // $issue['gitcommits_num'] = DB::collection('git_commits_' . $project_key)
        //     ->where('issue_id', $id)
        //     ->count();
        //
        // $issue['worklogs_num'] = Worklog::Where('project_key', $project_key)
        //     ->where('issue_id', $id)
        //     ->count();

        return $result;
    }

    public function createLabels(string $key, array $labels)
    {
        $models = di()->get(LabelDao::class)->findByName($key, $labels);
        $createdLabels = [];
        foreach ($models as $model) {
            $createdLabels[] = $model->name;
        }

        // get uncreated labels
        $labels = array_diff($labels, $createdLabels);
        foreach ($labels as $label) {
            $model = new Label();
            $model->project_key = $key;
            $model->name = $label;
            $model->save();
        }
        return true;
    }

    public function getValidKeysBySchema($schema = [])
    {
        $valid_keys = array_merge(array_column($schema, 'key'), ['type', 'assignee', 'descriptions', 'labels', 'parent_id', 'resolution', 'priority', 'progress', 'expect_start_time', 'expect_complete_time']);

        foreach ($schema as $field) {
            if ($field['type'] == Schema::FIELD_MULTI_USER) {
                $valid_keys[] = $field['key'] . '_ids';
            } elseif ($field['type'] == Schema::FIELD_TIME_TRACKING) {
                $valid_keys[] = $field['key'] . '_m';
            }
        }

        return $valid_keys;
    }

    public function index(Project $project, User $user)
    {
    }

    public function getAllOptions(int $userId, Project $project): array
    {
        $result = $this->getOptions($project);
        return array_merge($result, $this->otherOptions($userId, $project));
    }

    #[Cacheable(prefix: 'issue:options', value: '#{project.id}', ttl: 8640000, offset: 3600)]
    public function getOptions(Project $project)
    {
        return $this->options($project);
    }

    #[CachePut(prefix: 'issue:options', value: '#{project.id}', ttl: 8640000, offset: 3600)]
    public function putOptions(Project $project)
    {
        return $this->options($project);
    }

    public function options(Project $project)
    {
        $users = $this->provider->getUserList($project->key);
        $assignees = $this->provider->getAssignedUsers($project->key);
        $states = $this->provider->getStateListOptions($project->key);
        $resolutions = $this->provider->getResolutionOptions($project->key);
        $priorities = $this->provider->getPriorityOptions($project->key);
        $modules = $this->provider->getModuleList($project->key);
        $epics = $this->provider->getEpicList($project->key);
        $versions = $this->provider->getVersionList($project->key);
        $labels = $this->provider->getLabelOptions($project->key);
        $types = $this->provider->getTypeListExt($project->key);
        $sprints = $this->provider->getSprintList($project->key);
        $field = $this->provider->getFieldListOptions($project->key);
        $timeTrack = $this->provider->getTimeTrackSetting();
        $relations = $this->provider->getLinkRelations();

        return [
            'users' => $users,
            'assignees' => $assignees,
            'states' => $states,
            'resolutions' => $resolutions,
            'priorities' => $priorities,
            'modules' => $modules,
            'epics' => $epics,
            'versions' => $versions,
            'labels' => $labels,
            'types' => $types,
            'sprints' => $sprints,
            'fields' => $field,
            'timetrack' => $timeTrack,
            'relations' => $relations,
        ];
    }

    public function otherOptions(int $userId, Project $project): array
    {
        $filters = $this->provider->getIssueFilters($project->key, $userId);
        $displayColumns = $this->provider->getIssueDisplayColumns($project->key, $userId);
        return [
            'filters' => $filters,
            'display_columns' => $displayColumns,
        ];
    }

    #[AsyncQueueMessage(delay: 5)]
    public function pushToSearch(int $id): void
    {
        $model = di()->get(IssueDao::class)->first($id, false);

        $model?->pushToSearch();
    }

    public function getBoolSearch(string $projectKey, array $query, int $userId): array
    {
        $specialFields = [
            ['key' => 'no', 'type' => 'Number'],
            ['key' => 'type', 'type' => 'Select'],
            ['key' => 'state', 'type' => 'Select'],
            ['key' => 'assignee', 'type' => 'SingleUser'],
            ['key' => 'reporter', 'type' => 'SingleUser'],
            ['key' => 'resolver', 'type' => 'SingleUser'],
            ['key' => 'closer', 'type' => 'SingleUser'],

            ['key' => 'created_at', 'type' => 'Duration'],
            ['key' => 'updated_at', 'type' => 'Duration'],
            ['key' => 'resolved_at', 'type' => 'Duration'],
            ['key' => 'closed_at', 'type' => 'Duration'],

            ['key' => 'sprints', 'type' => 'Select'],
        ];

        $fields = $this->provider->getFieldList($projectKey);
        $fieldsArray = $fields->columns(['key', 'name', 'type'])->toArray();

        // merge into the all valid fields in the project
        $allFields = array_merge($fieldsArray, $specialFields);
        // convert into key-type array
        // $key_type_fields
        $fieldsMapping = [];
        foreach ($allFields as $val) {
            $fieldsMapping[$val['key']] = $val['type'];
        }
        // get the query where value
        $where = array_only($query, array_column($allFields, 'key'));

        $and = [];
        $bool = [];
        foreach ($where as $key => $val) {
            if ($key === 'no') {
                $bool['must'][] = ['term' => ['id' => intval($val)]];
            } elseif ($key === 'title') {
                if (is_numeric($val) && ! str_contains($val, '.')) {
                    $bool['must'][] = [
                        'bool' => [
                            'should' => [
                                [
                                    'term' => ['id' => intval($val)],
                                ],
                                [
                                    'match' => ['data.title' => $val],
                                ],
                            ],
                        ],
                    ];
                } elseif (str_contains($val, ',')) {
                    $nos = explode(',', $val);
                    $ids = [];
                    foreach ($nos as $no) {
                        if ($no && is_numeric($no)) {
                            $ids[] = intval($no);
                        }
                    }
                    $bool['must'][] = [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => ['id' => $ids],
                                ],
                                [
                                    'match' => ['data.title' => $val],
                                ],
                            ],
                        ],
                    ];
                } else {
                    $bool['must'][] = ['match' => ['data.title' => $val]];
                }
            } elseif ($key === 'sprints') {
                $bool['must'][] = ['term' => ['data.sprints' => intval($val)]];
            } elseif ($fieldsMapping[$key] === Schema::FIELD_SINGLE_USER) {
                $userIds = explode(',', $val);
                if (in_array('me', $userIds) && $userId) {
                    array_push($userIds, $userId);
                }
                $bool['must'][] = ['terms' => ['data.' . $key . '.' . 'id' => $userIds]];
            } elseif ($fieldsMapping[$key] === Schema::FIELD_MULTI_USER) {
                $userIds = [];
                $vals = explode(',', $val);
                foreach ($vals as $v) {
                    $userIds[] = $v == 'me' ? $userId : $v;
                }
                $bool['must'][] = [
                    'bool' => [
                        'should' => [
                            [
                                'terms' => ['data.' . $key . '_ids' => $userIds],
                            ],
                        ],
                    ],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_SELECT, Schema::FIELD_SINGLE_VERSION, Schema::FIELD_RADIO_GROUP])) {
                $bool['must'][] = [
                    'terms' => ['data.' . $key => explode(',', $val)],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_MULTI_SELECT, Schema::FIELD_MULTI_VERSION, Schema::FIELD_CHECKBOX_GROUP])) {
                $bool['must'][] = [
                    'bool' => [
                        'should' => [
                            [
                                'terms' => ['data.' . $key => $vals],
                            ],
                        ],
                    ],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_DURATION, Schema::FIELD_DATE_PICKER, Schema::FIELD_DATE_TIME_PICKER])) {
                if (in_array($val, ['0d', '0w', '0m', '0y'])) {
                    if ($val == '0d') {
                        $gte = strtotime(date('Y-m-d'));
                        $lte = strtotime(date('Y-m-d') . ' 23:59:59');
                    } elseif ($val == '0w') {
                        $gte = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y'));
                        $lte = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y'));
                    } elseif ($val == '0m') {
                        $gte = mktime(0, 0, 0, date('m'), 1, date('Y'));
                        $lte = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
                    } else {
                        $gte = mktime(0, 0, 0, 1, 1, date('Y'));
                        $lte = mktime(23, 59, 59, 12, 31, date('Y'));
                    }
                    $bool['must'][] = [
                        'range' => ['data.' . $key => ['gte' => $gte, 'lte' => $lte]],
                    ];
                } else {
                    $dateRange = [];
                    $unitMap = ['d' => 'day', 'w' => 'week', 'm' => 'month', 'y' => 'year'];
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $v = $sections[0];
                        $unit = substr($v, -1);
                        if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                            $direct = substr($v, 0, 1);
                            $vv = abs((float) substr($v, 0, -1));
                            $dateRange['gte'] = strtotime(date('Ymd', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])));
                        } else {
                            $dateRange['gte'] = strtotime($v);
                        }
                    }

                    if (isset($sections[1]) && $sections[1]) {
                        $v = $sections[1];
                        $unit = substr($v, -1);
                        if (in_array($unit, ['d', 'w', 'm', 'y'])) {
                            $direct = substr($v, 0, 1);
                            $vv = abs((float) substr($v, 0, -1));
                            $dateRange['lte'] = strtotime(date('Y-m-d', strtotime(($direct === '-' ? '-' : '+') . $vv . ' ' . $unitMap[$unit])) . ' 23:59:59');
                        } else {
                            $dateRange['lte'] = strtotime($v . ' 23:59:59');
                        }
                    }
                    $bool['must'][] = [
                        'range' => ['data.' . $key => $dateRange],
                    ];
                }
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_TEXT, Schema::FIELD_TEXT_AREA, Schema::FIELD_RICH_TEXT_EDITOR, Schema::FIELD_URL])) {
                $bool['must'][] = [
                    'match' => ['data.' . $key => $val],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_NUMBER, Schema::FIELD_INTEGER])) {
                if (str_contains($val, '~')) {
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $bool['must'][] = [
                            'range' => ['data.' . $key => ['gte' => intval($sections[0])]],
                        ];
                    }
                    if ($sections[1]) {
                        $bool['must'][] = [
                            'range' => ['data.' . $key => ['lte' => intval($sections[1])]],
                        ];
                    }
                }
            } elseif ($fieldsMapping[$key] === Schema::FIELD_TIME_TRACKING) {
                if (str_contains($val, '~')) {
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $bool['must'][] = [
                            'range' => ['data.' . $key . '_m' => ['gte' => $this->ttHandleInM($sections[0])]],
                        ];
                    }
                    if ($sections[1]) {
                        $bool['must'][] = [
                            'range' => ['data.' . $key . '_m' => ['lte' => $this->ttHandleInM($sections[0])]],
                        ];
                    }
                }
            }
        }

        if (isset($query['watcher']) && $query['watcher']) {
            // TODO: 支持 Watcher
            // $watcher = $query['watcher'] === 'me' ? $this->user->id : $query['watcher'];
            //
            // $watched_issues = Watch::where('project_key', $project_key)
            //     ->where('user.id', $watcher)
            //     ->get()
            //     ->toArray();
            // $watched_issue_ids = array_column($watched_issues, 'issue_id');
            //
            // $watchedIds = [];
            // foreach ($watched_issue_ids as $id) {
            //     $watchedIds[] = new ObjectID($id);
            // }
            // $and[] = ['_id' => ['$in' => $watchedIds]];
        }

        $bool['must_not'][] = [
            'term' => ['del_flg' => StatusConstant::DELETED],
        ];
        return $bool;
    }
}
