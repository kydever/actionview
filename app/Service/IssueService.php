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

use App\Events\IssueEvent;
use App\Model\Project;
use App\Model\User;
use App\Project\Provider;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Di\Annotation\Inject;
use Illuminate\Support\Facades\Event;

class IssueService extends Service
{
    #[Inject]
    protected ProviderService $provider;

    /**
     * @param $input = [
     *     'type' => '',
     * ]
     */
    public function store(array $input, User $user, Project $project)
    {
        $type = $input['type'];

        // $this->provider->getSc

        $schema = Provider::getSchemaByType($issue_type);
        if (! $schema) {
            throw new \UnexpectedValueException('the schema of the type is not existed.', -11101);
        }

        if (! $this->requiredCheck($schema, $request->all(), 'create')) {
            throw new \UnexpectedValueException('the required field is empty.', -11121);
        }

        // handle timetracking
        $insValues = [];
        foreach ($schema as $field) {
            $fieldValue = $request->input($field['key']);
            if (! isset($fieldValue) || ! $fieldValue) {
                continue;
            }

            if ($field['type'] == 'TimeTracking') {
                if (! $this->ttCheck($fieldValue)) {
                    throw new \UnexpectedValueException('the format of timetracking is incorrect.', -11102);
                }
                $insValues[$field['key']] = $this->ttHandle($fieldValue);
                $insValues[$field['key'] . '_m'] = $this->ttHandleInM($insValues[$field['key']]);
            } elseif ($field['type'] == 'DatePicker' || $field['type'] == 'DateTimePicker') {
                if ($this->isTimestamp($fieldValue) === false) {
                    throw new \UnexpectedValueException('the format of datepicker field is incorrect.', -11122);
                }
            } elseif ($field['type'] == 'SingleUser') {
                $user_info = Sentinel::findById($fieldValue);
                if ($user_info) {
                    $insValues[$field['key']] = ['id' => $fieldValue, 'name' => $user_info->first_name, 'email' => $user_info->email];
                }
            } elseif ($field['type'] == 'MultiUser') {
                $user_ids = $fieldValue;
                $new_user_ids = [];
                $insValues[$field['key']] = [];
                foreach ($user_ids as $uid) {
                    $user_info = Sentinel::findById($uid);
                    if ($user_info) {
                        array_push($insValues[$field['key']], ['id' => $uid, 'name' => $user_info->first_name, 'email' => $user_info->email]);
                        $new_user_ids[] = $uid;
                    }
                }
                $insValues[$field['key'] . '_ids'] = $new_user_ids;
            }
        }

        // handle assignee
        $assignee = [];
        $assignee_id = $request->input('assignee');
        if (! $assignee_id) {
            $module_ids = $request->input('module');
            if ($module_ids) {
                //$module_ids = explode(',', $module_ids);
                $module = Provider::getModuleById($module_ids[0]);
                if (isset($module['defaultAssignee']) && $module['defaultAssignee'] === 'modulePrincipal') {
                    $assignee2 = $module['principal'] ?: '';
                    $assignee_id = isset($assignee2['id']) ? $assignee2['id'] : '';
                } elseif (isset($module['defaultAssignee']) && $module['defaultAssignee'] === 'projectPrincipal') {
                    $assignee2 = Provider::getProjectPrincipal($project_key) ?: '';
                    $assignee_id = isset($assignee2['id']) ? $assignee2['id'] : '';
                }
            }
        }
        if ($assignee_id) {
            if ($assignee_id != $this->user->id && ! $this->isPermissionAllowed($project_key, 'assigned_issue', $assignee_id)) {
                return Response()->json(['ecode' => -11118, 'emsg' => 'the assigned user has not assigned-issue permission.']);
            }

            $user_info = Sentinel::findById($assignee_id);
            if ($user_info) {
                $assignee = ['id' => $assignee_id, 'name' => $user_info->first_name, 'email' => $user_info->email];
            }
        }
        if (! $assignee) {
            $assignee = ['id' => $this->user->id, 'name' => $this->user->first_name, 'email' => $this->user->email];
        }
        $insValues['assignee'] = $assignee;

        //$priority = $request->input('priority');
        //if (!isset($priority) || !$priority)
        //{
        //    $insValues['priority'] = Provider::getDefaultPriority($project_key);
        //}

        $resolution = $request->input('resolution');
        if (! isset($resolution) || ! $resolution) {
            $insValues['resolution'] = 'Unresolved';
        }

        // get reporter(creator)
        $insValues['reporter'] = ['id' => $this->user->id, 'name' => $this->user->first_name, 'email' => $this->user->email];
        $insValues['updated_at'] = $insValues['created_at'] = time();

        $table = 'issue_' . $project_key;
        $max_no = DB::collection($table)->count() + 1;
        $insValues['no'] = $max_no;

        // workflow initialize
        $workflow = $this->initializeWorkflow($issue_type);
        $insValues = $insValues + $workflow;

        $valid_keys = $this->getValidKeysBySchema($schema);
        // merge all fields
        $insValues = $insValues + array_only($request->all(), $valid_keys);

        // insert into the table
        $id = DB::collection($table)->insertGetId($insValues);

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

    public function index(Project $project, User $user)
    {
    }

    #[Cacheable(prefix: 'issue:options', value: '#{$project.id}', ttl: 86400, offset: 3600)]
    public function getOptions(Project $project)
    {
        return $this->options($project);
    }

    #[CachePut(prefix: 'issue:options', value: '#{$project.id}', ttl: 86400, offset: 3600)]
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

        return [
            'user' => $users,
            'assignees' => $assignees,
            'states' => $states,
            'resolutions' => $resolutions,
            'priorities' => $priorities,
            'modules' => $modules,
            'epics' => $epics,
            'versions' => $versions,
        ];
    }
}
