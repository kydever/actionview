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
use App\Model\IssueFilter;
use App\Model\Label;
use App\Model\Project;
use App\Model\User;
use App\Model\UserIssueFilter;
use App\Project\Eloquent\Labels;
use App\Project\Provider;
use App\Service\Client\IssueSearch;
use App\Service\Dao\ConfigScreenDao;
use App\Service\Dao\IssueDao;
use App\Service\Dao\IssueFilterDao;
use App\Service\Dao\LabelDao;
use App\Service\Dao\ModuleDao;
use App\Service\Dao\OswfEntryDao;
use App\Service\Dao\ProjectDao;
use App\Service\Dao\UserDao;
use App\Service\Dao\UserIssueFilterDao;
use App\Service\Formatter\IssueFormatter;
use App\Service\Formatter\UserFormatter;
use App\Service\Struct\Workflow;
use Han\Utils\Service;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use Illuminate\Support\Facades\Event;
use Psr\EventDispatcher\EventDispatcherInterface;
use function issue_key as ik;

class IssueService extends Service
{
    use TimeTrackTrait;

    #[Inject]
    protected IssueDao $dao;

    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected IssueFormatter $formatter;

    #[Inject]
    protected IssueSearch $search;

    #[Inject]
    protected AclService $acl;

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

    public function update(int $id, array $input, User $user, Project $project): array
    {
        $issue = $this->dao->first($id, true);
        if (empty($input)) {
            return $this->show($issue, $user, $project);
        }

        $type = intval($input['type'] ?? $issue->type);
        $assigneeId = intval($input['assignee'] ?? null);
        $assignee = null;
        $resolution = $input['resolution'] ?? null;

        $isAllowed = $this->acl->isAllowed($user->id, Permission::EDIT_ISSUE, $project)
            || ($this->acl->isAllowed($user->id, Permission::EDIT_SELF_ISSUE, $project) && ($issue->reporter['id'] ?? null) == $user->id)
            || $this->acl->isAllowed($user->id, Permission::EXEC_WORKFLOW, $project);

        if (! $isAllowed) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $schema = $this->provider->getSchemaByType($type);
        if (! $schema) {
            throw new BusinessException(ErrorCode::ISSUE_TYPE_SCHEMA_NOT_EXIST);
        }

        if (! $this->requiredCheck($schema, $input, 'update')) {
            throw new BusinessException(ErrorCode::ISSUE_TYPE_SCHEMA_REQUIRED);
        }

        $updValues = [];
        foreach ($schema as $field) {
            $fieldValue = $input[$field['key']] ?? null;
            if (! $fieldValue) {
                continue;
            }

            if ($field['type'] == Schema::FIELD_TIME_TRACKING) {
                if (! $this->ttCheck($fieldValue)) {
                    throw new BusinessException(ErrorCode::ISSUE_TIME_TRACKING_INVALID);
                }

                $updValues[$field['key']] = $this->ttHandle($fieldValue);
                $updValues[$field['key'] . '_m'] = $this->ttHandleInM($updValues[$field['key']]);
            } elseif ($field['type'] == Schema::FIELD_DATE_PICKER || $field['type'] == Schema::FIELD_DATE_TIME_PICKER) {
                if ($this->isTimestamp($fieldValue) === false) {
                    throw new BusinessException(ErrorCode::ISSUE_DATE_TIME_PICKER_INVALID);
                }
            } elseif ($field['type'] == Schema::FIELD_SINGLE_USER) {
                $userModel = di()->get(UserDao::class)->first($fieldValue);
                if ($userModel) {
                    $updValues[$field['key']] = di()->get(UserFormatter::class)->base($userModel);
                }
            } elseif ($field['type'] == Schema::FIELD_MULTI_USER) {
                $userModels = di()->get(UserDao::class)->findMany($fieldValue);
                $userIds = $userModels->columns('id')->toArray();
                $updValues[$field['key']] = di()->get(UserFormatter::class)->formatList($userModels);
                $updValues[$field['key'] . '_ids'] = $userIds;
            }
        }

        if ($assigneeId && $assigneeId !== ($issue->assignee['id'] ?? null)) {
            if (! $this->acl->isAllowed($assigneeId, Permission::ASSIGNED_ISSUE, $project)) {
                throw new BusinessException(ErrorCode::ASSIGNED_USER_PERMISSION_DENIED);
            }

            $userModel = di()->get(UserDao::class)->first($assigneeId);
            if ($userModel) {
                $assignee = di()->get(UserFormatter::class)->base($userModel);
                $updValues['assignee'] = $assignee;
            }
        }

        $updValues = $updValues + Arr::only($input, $this->getValidKeysBySchema($schema));
        if (! $updValues) {
            return $this->show($issue, $user, $project);
        }

        $updValues['modifier'] = $user->toSmall();

        $updValues = array_replace($issue->data, $updValues);

        $issue->type = $type;
        $issue->modifier = $user->toSmall();
        $issue->data = $updValues;
        $resolution && $issue->resolution = $resolution;
        $assignee && $issue->assignee = $assignee;

        Db::beginTransaction();
        try {
            $issue->save();
            // TODO: History table
            di()->get(EventDispatcherInterface::class)->dispatch(new IssueEvent($issue));
            if (isset($updValues['labels']) && $updValues['labels']) {
                $this->createLabels($project->key, $updValues['labels']);
            }
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->show($issue, $user, $project);
    }

    /**
     * @param $input = [
     *     'type' => '',
     * ]
     */
    public function store(array $input, User $user, Project $project): array
    {
        $type = (int) $input['type'];
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

        Db::beginTransaction();
        try {
            // Support Workflow
            $workflow = $this->initializeWorkflow($type, $user);
            $insValues = $insValues + $workflow;
            $insValues = $insValues + Arr::only($input, $this->getValidKeysBySchema($schema));

            $model = new Issue();
            $model->project_key = $project->key;
            $model->type = $type;
            $model->del_flg = StatusConstant::NOT_DELETED;
            $model->resolution = $resolution ?: StatusConstant::STATUS_UNRESOLVED;
            $model->assignee = $assignee;
            $model->reporter = di()->get(UserFormatter::class)->small($user);
            $model->modifier = di()->get(UserFormatter::class)->small($user);
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

        return $this->show($model, $user, $project);
    }

    public function show(Issue $issue, User $user, Project $project): array
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

        // 获取可用的 workflow
        if (! empty($issue->data['entry_id']) && di()->get(AclService::class)->isAllowed($user->id, Permission::EXEC_WORKFLOW, $project)) {
            try {
                $entry = di()->get(OswfEntryDao::class)->first($issue->data['entry_id'], false);
                $wf = new Workflow($entry);
                $result['wfactions'] = $wf->getAvailableActions(['project_key' => $project->key, 'issue_id' => $issue->id, 'caller' => $user->toSmall()]);
            } catch (\Throwable $exception) {
                di()->get(StdoutLoggerInterface::class)->error((string) $exception);
                $result['wfactions'] = [];
            }

            $screenIds = array_column($result['wfactions'], 'screen');
            $screens = [];
            if ($screenIds) {
                $screens = di()->get(ConfigScreenDao::class)->findMany($screenIds)->getDictionary();
            }

            foreach ($result['wfactions'] as $key => $action) {
                if (isset($action['screen']) && $action['screen'] && $action['screen'] != 'comments' && isset($screens[$action['screen']])) {
                    $result['wfactions'][$key]['schema'] = $this->provider->getScreenSchema($project->key, $issue->type, $screens[$action['screen']]);
                }
            }
        }

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

    public function createLabels(string $key, array $labels): bool
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

    public function getValidKeysBySchema($schema = []): array
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

    public function index(array $input, Project $project, User $user, int $offset, int $limit)
    {
        $bool = $this->getBoolSearch($project->key, $input, $user->id);

        [$count, $ids] = $this->search->search([
            'query' => [
                'bool' => $bool,
            ],
            'sort' => ['created_at' => 'desc'],
            'from' => $offset,
            'size' => $limit,
        ]);

        $models = $this->dao->findMany($ids);

        $result = $this->formatter->formatList($models);

        $options = ['total' => $count, 'sizePerPage' => $limit];

        return [$count, $result, $options];
        // TODO: 看板
        // $from = $request->input('from');
        // $from_kanban_id = $request->input('from_kanban_id');
        // if (in_array($from, [ 'kanban', 'active_sprint', 'backlog' ]) && $from_kanban_id)
        // {
        //     $board = Board::find($from_kanban_id);
        //     if ($board && isset($board->query) && $board->query)
        //     {
        //         $global_query = $this->getIssueQueryWhere($project_key, $board->query);
        //         $query->whereRaw($global_query);
        //     }
        //
        //     if ($from === 'kanban')
        //     {
        //         $query->where(function ($query) {
        //             $query->whereRaw([ 'resolve_version' => [ '$exists' => 0 ] ])->orWhere('resolve_version', '');
        //         });
        //     }
        //     else if ($from === 'active_sprint' || $from === 'backlog')
        //     {
        //         $active_sprint_issues = [];
        //         $active_sprint = Sprint::where('project_key', $project_key)->where('status', 'active')->first();
        //         if ($from === 'active_sprint' && !$active_sprint)
        //         {
        //             Response()->json([ 'ecode' => 0, 'data' => []]);
        //         }
        //         else if ($active_sprint && isset($active_sprint['issues']) && $active_sprint['issues'])
        //         {
        //             $active_sprint_issues = $active_sprint['issues'];
        //         }
        //
        //         $last_column_states = [];
        //         if ($board && isset($board->columns))
        //         {
        //             $board_columns = $board->columns;
        //             $last_column = array_pop($board_columns) ?: [];
        //             if ($last_column && isset($last_column['states']) && $last_column['states'])
        //             {
        //                 $last_column_states = $last_column['states'];
        //             }
        //         }
        //
        //         $query->where(function ($query) use ($last_column_states, $active_sprint_issues) {
        //             $query->whereRaw([ 'state' => [ '$nin' => $last_column_states ] ])->orWhereIn('no', $active_sprint_issues);
        //         });
        //     }
        // }

        // get total num
        $total = $query->count();

        $orderBy = $request->input('orderBy') ?: '';
        if ($orderBy) {
            $orderBy = explode(',', $orderBy);
            foreach ($orderBy as $val) {
                $val = explode(' ', trim($val));
                $field = array_shift($val);
                $sort = array_pop($val) ?: 'asc';
                $query = $query->orderBy($field, $sort);
            }
        }

        $query->orderBy('_id', isset($from) && $from != 'gantt' ? 'asc' : 'desc');

        $page_size = $request->input('limit') ? intval($request->input('limit')) : 50;
        //$page_size = 200;
        $page = $request->input('page') ?: 1;
        $query = $query->skip($page_size * ($page - 1))->take($page_size);
        $issues = $query->get();

        if ($from == 'export') {
            $export_fields = $request->input('export_fields');
            $this->export($project_key, isset($export_fields) ? explode(',', $export_fields) : [], $issues);
            exit();
        }

        $watched_issue_ids = [];
        if (! isset($from)) {
            $watched_issues = Watch::where('project_key', $project_key)
                ->where('user.id', $this->user->id)
                ->get()
                ->toArray();
            $watched_issue_ids = array_column($watched_issues, 'issue_id');
        }

        $cache_parents = [];
        $issue_ids = [];
        foreach ($issues as $key => $issue) {
            $issue_ids[] = $issue['_id']->__toString();
            // set issue watching flag
            if (in_array($issue['_id']->__toString(), $watched_issue_ids)) {
                $issues[$key]['watching'] = true;
            }

            // get the parent issue
            if (isset($issue['parent_id']) && $issue['parent_id']) {
                if (isset($cache_parents[$issue['parent_id']]) && $cache_parents[$issue['parent_id']]) {
                    $issues[$key]['parent'] = $cache_parents[$issue['parent_id']];
                } else {
                    $parent = DB::collection('issue_' . $project_key)->where('_id', $issue['parent_id'])->first();
                    $issues[$key]['parent'] = $parent ? Arr::only($parent, ['_id', 'title', 'no', 'type', 'state']) : [];
                    $cache_parents[$issue['parent_id']] = $issues[$key]['parent'];
                }
            }

            if (! isset($from)) {
                $issues[$key]['hasSubtasks'] = DB::collection('issue_' . $project_key)
                    ->where('parent_id', $issue['_id']->__toString())
                    ->where('del_flg', '<>', 1)
                    ->exists();
            }
        }

        if ($issues && in_array($from, ['kanban', 'active_sprint', 'backlog', 'his_sprint'])) {
            $filter = $request->input('filter') ?: '';
            $issues = $this->arrangeIssues($project_key, $issues, $from, $from_kanban_id, $filter === 'all');
        }

        $options = ['total' => $total, 'sizePerPage' => $page_size];

        if ($from == 'gantt') {
            foreach ($issues as $key => $issue) {
                $issues[$key]['links'] = $this->getLinks($project_key, $issue);
                if (! isset($issue['parent_id']) || ! $issue['parent_id'] || in_array($issue['parent_id'], $issue_ids)) {
                    continue;
                }
                if (isset($issue['parent']) && $issue['parent']) {
                    $issues[] = $issue['parent'];
                    $issue_ids[] = $issue['parent_id'];
                }
            }

            $singulars = CalendarSingular::all();
            $new_singulars = [];
            foreach ($singulars as $singular) {
                $tmp = [];
                $tmp['notWorking'] = $singular->type == 'holiday' ? 1 : 0;
                $tmp['date'] = $singular->date;
                $new_singulars[] = $tmp;
            }
            $options['singulars'] = $new_singulars;
            $options['today'] = date('Y/m/d');
        }

        $requested_at = $request->input('requested_at');
        if ($requested_at) {
            $options['requested_at'] = intval($requested_at);
        }

        return Response()->json(['ecode' => 0, 'data' => parent::arrange($issues), 'options' => $options]);
    }

    public function getAllOptions(int $userId, Project $project): array
    {
        $result = $this->getOptions($project);
        return array_merge($result, $this->otherOptions($userId, $project));
    }

    #[Cacheable(prefix: 'issue:options', value: '#{project.id}', ttl: 8640000, offset: 3600)]
    public function getOptions(Project $project): array
    {
        return $this->options($project);
    }

    #[CachePut(prefix: 'issue:options', value: '#{project.id}', ttl: 8640000, offset: 3600)]
    public function putOptions(Project $project): array
    {
        return $this->options($project);
    }

    #[AsyncQueueMessage(delay: 5)]
    public function putOptionsAsync(string $projectKey): ?array
    {
        $project = di()->get(ProjectDao::class)->firstByKey($projectKey, false);
        if ($project) {
            $this->putOptions($project);
        }
    }

    public function options(Project $project): array
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
        $where = Arr::only($query, array_column($allFields, 'key'));

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
                                    'match' => [ik('title') => $val],
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
                                    'match' => [ik('title') => $val],
                                ],
                            ],
                        ],
                    ];
                } else {
                    $bool['must'][] = ['match' => [ik('title') => $val]];
                }
            } elseif ($key === 'sprints') {
                $bool['must'][] = ['term' => [ik('sprints') => intval($val)]];
            } elseif ($fieldsMapping[$key] === Schema::FIELD_SINGLE_USER) {
                $userIds = explode(',', $val);
                if (in_array('me', $userIds) && $userId) {
                    $userIds[] = $userId;
                }
                $userIds = array_values(array_filter($userIds, static function ($value) {
                    return is_numeric($value);
                }));
                $bool['must'][] = ['terms' => [ik($key . '.' . 'id') => $userIds]];
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
                                'terms' => [ik($key . '_ids') => $userIds],
                            ],
                        ],
                    ],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_SELECT, Schema::FIELD_SINGLE_VERSION, Schema::FIELD_RADIO_GROUP])) {
                $bool['must'][] = [
                    'terms' => [ik($key) => explode(',', $val)],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_MULTI_SELECT, Schema::FIELD_MULTI_VERSION, Schema::FIELD_CHECKBOX_GROUP])) {
                $vals = explode(',', $val);
                $bool['must'][] = [
                    'bool' => [
                        'should' => [
                            [
                                'terms' => [ik($key) => $vals],
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
                        $gte = mktime(0, 0, 0, (int) date('m'), (int) date('d') - (int) date('w') + 1, (int) date('Y'));
                        $lte = mktime(23, 59, 59, (int) date('m'), (int) date('d') - (int) date('w') + 7, (int) date('Y'));
                    } elseif ($val == '0m') {
                        $gte = mktime(0, 0, 0, (int) date('m'), 1, (int) date('Y'));
                        $lte = mktime(23, 59, 59, (int) date('m'), (int) date('t'), (int) date('Y'));
                    } else {
                        $gte = mktime(0, 0, 0, 1, 1, (int) date('Y'));
                        $lte = mktime(23, 59, 59, 12, 31, (int) date('Y'));
                    }
                    $bool['must'][] = [
                        'range' => [ik($key) => ['gte' => $gte, 'lte' => $lte]],
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
                        'range' => [ik($key) => $dateRange],
                    ];
                }
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_TEXT, Schema::FIELD_TEXT_AREA, Schema::FIELD_RICH_TEXT_EDITOR, Schema::FIELD_URL])) {
                $bool['must'][] = [
                    'match' => [ik($key) => $val],
                ];
            } elseif (in_array($fieldsMapping[$key], [Schema::FIELD_NUMBER, Schema::FIELD_INTEGER])) {
                if (str_contains($val, '~')) {
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $bool['must'][] = [
                            'range' => [ik($key) => ['gte' => intval($sections[0])]],
                        ];
                    }
                    if ($sections[1]) {
                        $bool['must'][] = [
                            'range' => [ik($key) => ['lte' => intval($sections[1])]],
                        ];
                    }
                }
            } elseif ($fieldsMapping[$key] === Schema::FIELD_TIME_TRACKING) {
                if (str_contains($val, '~')) {
                    $sections = explode('~', $val);
                    if ($sections[0]) {
                        $bool['must'][] = [
                            'range' => [ik($key . '_m') => ['gte' => $this->ttHandleInM($sections[0])]],
                        ];
                    }
                    if ($sections[1]) {
                        $bool['must'][] = [
                            'range' => [ik($key . '_m') => ['lte' => $this->ttHandleInM($sections[0])]],
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
        $bool['must'][] = ['term' => ['project_key' => $projectKey]];

        return $bool;
    }

    public function setAssignee(int $id, string $assigneeId, User $user, Project $project): array|User
    {
        $issue = $this->dao->first($id, true);
        $isAllowed = di()->get(AclService::class)->isAllowed($user->id, Permission::ASSIGN_ISSUE, $project);

        if (! $isAllowed) {
            throw new BusinessException(ErrorCode::ASSIGN_ASSIGNEE_DENIED);
        }

        $userModel = $this->getAssignee($assigneeId, $issue, $user, $project);
        if (is_array($userModel)) {
            return $userModel;
        }

        $assignee = di()->get(UserFormatter::class)->small($userModel);
        $modifier = di()->get(UserFormatter::class)->small($user);

        $issue->assignee = $assignee;
        $issue->modifier = $modifier;

        Db::beginTransaction();
        try {
            $issue->save();
            di()->get(EventDispatcherInterface::class)->dispatch(new IssueEvent($issue));
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->show($issue, $user, $project);
    }

    public function resetState(array $input, int $id, User $user, Project $project): array
    {
        $assigneeId = intval($input['assignee'] ?? null);
        $resolution = $input['resolution'] ?? null;
        $assignee = null;
        if ($assigneeId) {
            $isAllowed = di()->get(AclService::class)->isAllowed($assigneeId, Permission::ASSIGNED_ISSUE, $project);
            if (! $isAllowed) {
                throw new BusinessException(ErrorCode::ASSIGNED_USER_PERMISSION_DENIED);
            }

            $assignee = di()->get(UserFormatter::class)->base(
                di()->get(UserDao::class)->first($assigneeId, true)
            );
        }

        $issue = $this->dao->first($id, true);
        $assignee && $issue->assignee = $assignee;
        $resolution && $issue->resolution = $resolution;
        $issue->modifier = di()->get(UserFormatter::class)->small($user);

        Db::beginTransaction();
        try {
            $issue->save();
            // TODO: 初始化 workflow

            // TODO: Histroy Table

            di()->get(EventDispatcherInterface::class)->dispatch(new IssueEvent($issue));
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->show($issue, $user, $project);
    }

    /**
     * @param $input = [
     *     'name' => '',
     * ]
     */
    public function saveIssueFilter(array $input, User $user, Project $project)
    {
        $name = $input['name'];
        $query = $input['query'] ?? [];
        $scope = StatusConstant::SCOPE_STRING_PRIVATE;

        if (di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project)) {
            $scope = $input['scope'] ?? StatusConstant::SCOPE_STRING_PRIVATE;
        }

        $model = new IssueFilter();
        $model->project_key = $project->key;
        $model->name = $name;
        $model->query = $query;
        $model->scope = $scope;
        $model->creator = di()->get(UserFormatter::class)->small($user);
        $model->save();

        return $this->getIssueFilters($project, $user);
    }

    public function getIssueFilters(Project $project, User $user): array
    {
        return $this->provider->getIssueFilters($project->key, $user->id);
    }

    public function resetIssueFilters(Project $project, User $user): array
    {
        di()->get(IssueFilterDao::class)->delete($project->key, $user->id);

        return $this->provider->getIssueFilters($project->key, $user->id);
    }

    /**
     * @param $input = [
     *     'mode' => 'sort',
     *     'sequence' => [],
     *     'ids' => [],
     * ]
     */
    public function batchHandleFilters(array $input, User $user, Project $project): array
    {
        return match ($input['mode'] ?? null) {
            'sort' => $this->sortFilters($input['sequence'] ?? [], $user, $project),
            'del' => $this->delFilters($input['ids'] ?? [], $user, $project),
            default => $this->getIssueFilters($project, $user),
        };
    }

    public function doAction(int $id, int $workflowId, array $input, User $user, Project $project): array
    {
        $actionId = (int) ($input['action_id'] ?? 0);
        if (empty($actionId)) {
            throw new BusinessException(ErrorCode::ISSUE_DO_ACTION_ID_CANNOT_EMPTY);
        }

        $issue = $this->dao->first($id, true);

        try {
            $entry = new Workflow(di()->get(OswfEntryDao::class)->first($workflowId));
            $entry->doAction(
                $actionId,
                [
                    'project_key' => $project->key,
                    'issue_id' => $id,
                    'issue' => $issue,
                    'caller' => $user->toSmall(),
                ] + Arr::only($input, ['comments'])
            );

            $issue->pushToSearch();
        } catch (\Throwable $e) {
            di()->get(StdoutLoggerInterface::class)->warning((string) $e);
            throw new BusinessException(ErrorCode::ISSUE_DO_ACTION_ID_CANNOT_EMPTY);
        }
        return $this->show($issue, $user, $project);
    }

    protected function initializeWorkflow(int $type, User $user): array
    {
        $definition = $this->provider->getWorkflowByType($type);
        // create and start workflow instacne
        $workflow = Workflow::createInstance($definition, $user)->start(['caller' => $user->toSmall()]);
        // get the inital step
        $step = $workflow->getCurrentSteps()->first();
        $state = $workflow->getStepMeta($step->step_id, 'state');

        return [
            'state' => $state,
            'entry_id' => $workflow->getEntryId(),
            'definition_id' => $definition->id,
        ];
    }

    protected function delFilters(array $ids, User $user, Project $project): array
    {
        if ($ids) {
            $models = di()->get(IssueFilterDao::class)->findMany($ids);
            foreach ($models as $model) {
                $model->delete();
            }
        }

        return $this->getIssueFilters($project, $user);
    }

    protected function sortFilters(array $sequence, User $user, Project $project): array
    {
        if (! empty($sequence)) {
            $model = di()->get(UserIssueFilterDao::class)->getUserFilter($project->key, $user->id);
            if (! $model) {
                $model = new UserIssueFilter();
                $model->project_key = $project->key;
                $model->user = $user->toSmall();
            }
            $model->sequence = $sequence;
            $model->save();
        }

        return $this->getIssueFilters($project, $user);
    }

    private function getAssignee(string $assigneeId, Issue $issue, User $user, Project $project): array|User
    {
        $assigneeId = match ($assigneeId) {
            'me' => $user->id,
            default => (int) $assigneeId
        };

        if ($issue->assignee['id'] === $assigneeId) {
            return $this->show($issue, $user, $project);
        }

        $userModel = $assigneeId === $user->id ? $user : di()->get(UserDao::class)->first($assigneeId, true);
        $isAllowed = di()->get(AclService::class)->isAllowed($userModel->id, Permission::ASSIGNED_ISSUE, $project);
        if (! $isAllowed) {
            throw new BusinessException(ErrorCode::ASSIGNED_ASSIGNEE_DENIED);
        }

        return $userModel;
    }
}
