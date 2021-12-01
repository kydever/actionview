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
use App\Events\IssueEvent;
use App\Exception\BusinessException;
use App\Model\Issue;
use App\Model\Project;
use App\Model\User;
use App\Project\Provider;
use App\Service\Dao\IssueDao;
use App\Service\Dao\ModuleDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Di\Annotation\Inject;
use Illuminate\Support\Facades\Event;

class IssueService extends Service
{
    use TimeTrackTrait;

    #[Inject]
    protected ProviderService $provider;

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
            if ($assigneeId !== $user->id && ! di()->get(AclService::class)->hasAccess($assigneeId, $project, Permission::ISSUE_ASSIGNED)) {
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

        $valid_keys = $this->getValidKeysBySchema($schema);
        $insValues = $insValues + array_only($input, $valid_keys);

        $model = new Issue();
        $model->project_key = $project->key;
        $model->del_flg = StatusConstant::NOT_DELETED;
        $model->resolution = $resolution ?: StatusConstant::STATUS_UNRESOLVED;
        $model->assignee = $assignee;
        $model->reporter = di()->get(UserFormatter::class)->small($user);
        $model->no = $maxNumber;
        $model->save();

        return [];
        // add to histroy table
        Provider::snap2His($project_key, $id, $schema);
        // trigger event of issue created
        Event::fire(new IssueEvent($project_key, $id->__toString(), $insValues['reporter'], ['event_key' => 'create_issue']));

        // create the Labels for project
        if (isset($insValues['labels']) && $insValues['labels']) {
            $this->createLabels($project_key, $insValues['labels']);
        }

        return $this->show($project_key, $id->__toString());
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

    #[Cacheable(prefix: 'issue:options', value: '#{project.id}', ttl: 86400, offset: 3600)]
    public function getOptions(Project $project)
    {
        return $this->options($project);
    }

    #[CachePut(prefix: 'issue:options', value: '#{project.id}', ttl: 86400, offset: 3600)]
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
        $field = $this->provider->getFieldList($project->key);

        return [
            'user' => $users,
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
            'field' => $field,
        ];
    }

    public function otherOptions(int $userId, Project $project): array
    {
        $filters = $this->provider->getIssueFilters($project->key, $userId);

        return [
            'filters' => $filters,
        ];
    }
}
