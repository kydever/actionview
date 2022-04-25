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
namespace App\Workflow;

use App\Acl\Acl;
use App\Events\IssueEvent;
use App\Model\User;
use App\Project\Provider;
use App\Service\AclService;
use App\Service\Context\ProjectContext;
use App\Service\Context\UserContext;
use App\Service\Dao\IssueDao;
use App\Service\ProjectAuth;
use DB;
use Hyperf\Utils\Arr;
use Illuminate\Support\Facades\Event;

class Func
{
    public static $snap_id = '';

    public static function getIssueProperties()
    {
        return IssueProperty::instance();
    }

    /**
     * check if user is the type.
     *
     * @param array $param
     * @return bool
     */
    public static function isSome($param)
    {
        $issue_id = $param['issue_id'];
        $project_key = $param['project_key'];
        $caller = $param['caller'];

        if ($param['someParam'] == 'assignee') {
            $issue = DB::collection('issue_' . $project_key)->where('_id', $issue_id)->first();
            if ($issue && isset($issue['assignee'], $issue['assignee']['id']) && $issue['assignee']['id'] === $caller) {
                return true;
            }
        } elseif ($param['someParam'] == 'reporter') {
            $issue = DB::collection('issue_' . $project_key)->where('_id', $issue_id)->first();
            if ($issue && isset($issue['reporter'], $issue['reporter']['id']) && $issue['reporter']['id'] === $caller) {
                return true;
            }
        } elseif ($param['someParam'] == 'principal') {
            $principal = Provider::getProjectPrincipal($project_key) ?: [];
            return $principal && $principal['id'] === $caller;
        }

        return false;
    }

    /**
     * check if user is the user.
     *
     * @param array $param
     * @return bool
     */
    public static function isTheUser($param)
    {
        return $param['caller'] === $param['userParam'];
    }

    /**
     * check if subtask's state .
     *
     * @param array $param
     * @return bool
     */
    public static function checkSubTasksState($param)
    {
        $issue_id = $param['issue_id'];
        $project_key = $param['project_key'];

        $subtasks = DB::collection('issue_' . $project_key)->where('parent_id', $issue_id)->get(['state']);
        foreach ($subtasks as $subtask) {
            if ($subtask['state'] != $param['stateParam']) {
                return false;
            }
        }

        return true;
    }

    /**
     * check if user has permission allow.
     *
     * @param array $param
     * @return bool
     */
    public static function hasPermission($param)
    {
        $project_key = $param['project_key'];
        $caller = $param['caller'];
        $permission = $param['permissionParam'];

        // 检查是否是当前项目
        $project = ProjectAuth::instance()->getCurrent();
        if ($project->key !== $project_key) {
            $project = ProjectContext::instance()->first($project_key, true);
        }

        return di()->get(AclService::class)->isAllowed(is_numeric($caller) ? (int) $caller : (int) $caller['id'], $permission, $project);
    }

    /**
     * check if user belongs to the role.
     *
     * @param array $param
     * @return bool
     */
    public static function belongsToRole($param)
    {
        $project_key = $param['project_key'];
        $caller = $param['caller'];

        $roles = Acl::getRolesByUid($project_key, $caller);
        foreach ($roles as $role) {
            if ($role === $param['roleParam']) {
                return true;
            }
        }
        return false;
    }

    /**
     * set resolution value to issue_properties.
     *
     * @param array $param
     */
    public static function setResolution($param)
    {
        if (isset($param['resolutionParam']) && $param['resolutionParam']) {
            self::getIssueProperties()->data['resolution'] = $param['resolutionParam'];
        }
    }

    /**
     * set progress value to issue_properties.
     *
     * @param array $param
     */
    public static function setProgress($param)
    {
        if (isset($param['progressParam']) && $param['progressParam']) {
            self::getIssueProperties()->data['progress'] = intval($param['progressParam']);
        }
    }

    /**
     * set state value to issue_properties.
     *
     * @param array $param
     */
    public static function setState($param)
    {
        if (isset($param['state']) && $param['state']) {
            self::getIssueProperties()->data['state'] = $param['state'];
        }
    }

    /**
     * set assignee value to issue_properties.
     *
     * @param array $param
     */
    public static function assignIssueToUser($param)
    {
        $user_info = UserContext::instance()->first($param['assignedUserParam'], true);
        if ($user_info) {
            self::getIssueProperties()->data['assignee'] = ['id' => $user_info->id, 'name' => $user_info->first_name];
        }
    }

    /**
     * set assignee value to issue_properties.
     *
     * @param array $param
     */
    public static function assignIssue($param)
    {
        $project_key = $param['project_key'];
        $issue_id = $param['issue_id'];
        $caller = $param['caller'];

        if ($param['assigneeParam'] == 'me') {
            $user_info = UserContext::instance()->first($caller['id'], true);
            if ($user_info) {
                self::getIssueProperties()->data['assignee'] = ['id' => $user_info->id, 'name' => $user_info->first_name];
            }
        } elseif ($param['assigneeParam'] == 'reporter') {
            $issue = DB::collection('issue_' . $project_key)->where('_id', $issue_id)->first();
            if ($issue && isset($issue['reporter'])) {
                self::getIssueProperties()->data['assignee'] = $issue['reporter'];
            }
        } elseif ($param['assigneeParam'] == 'principal') {
            $principal = Provider::getProjectPrincipal($project_key) ?: [];
            if ($principal) {
                self::getIssueProperties()->data['assignee'] = $principal;
            }
        }
    }

    /**
     * update issue.
     *
     * @param array $param
     */
    public static function addComments($param)
    {
        $issue_id = $param['issue_id'];
        $project_key = $param['project_key'];
        $caller = $param['caller'];
        $comments = isset($param['comments']) ? $param['comments'] : '';

        if (! $comments) {
            return;
        }

        $user_info = UserContext::instance()->first($caller['id'], true);
        $creator = ['id' => $user_info->id, 'name' => $user_info->first_name, 'email' => $user_info->email];

        $table = 'comments_' . $project_key;
        DB::collection($table)->insert(['contents' => $comments, 'atWho' => [], 'issue_id' => $issue_id, 'creator' => $creator, 'created_at' => time()]);

        // trigger event of comments added
        Event::fire(new IssueEvent($project_key, $issue_id, $creator, ['event_key' => 'add_comments', 'data' => ['contents' => $comments, 'atWho' => []]]));
    }

    /**
     * update issue.
     *
     * @param array $param
     */
    public static function updIssue($param)
    {
        $issue_id = $param['issue_id'];
        $issue = $param['issue'] ?? di()->get(IssueDao::class)->first($issue_id);
        $project_key = $param['project_key'];
        $caller = $param['caller'];

        if (count(self::getIssueProperties()->data) > 0) {
            $updValues = $issue->data;
            $updValues = array_replace($updValues, self::getIssueProperties()->data);
            /** @var User $user_info */
            $user_info = UserContext::instance()->first($caller['id'], true);
            $issue->modifier = $user_info->toSmall();
            $issue->data = $updValues;
            $issue->save();
        }
    }

    /**
     * trigger issue.event.
     *
     * @param array $param
     */
    public static function triggerEvent($param)
    {
        $issue_id = $param['issue_id'];
        $issue = $param['issue'] ?? di()->get(IssueDao::class)->first($issue_id);
        $event_key = Arr::get($param, 'eventParam', 'normal');

        /** @var User $user_info */
        $user_info = UserContext::instance()->first($param['caller']['id'], true);
        $caller = $user_info->toSmall();

        $updValues = [];
        if ($event_key === 'resolve_issue') {
            $updValues['resolved_at'] = time();
            $updValues['resolver'] = $caller;

            if (isset($issue['regression_times']) && $issue['regression_times']) {
                $updValues['regression_times'] = $issue['regression_times'] + 1;
            } else {
                $updValues['regression_times'] = 1;
            }

            $logs = [];
            if (isset($issue['resolved_logs']) && $issue['resolved_logs']) {
                $logs = $issue['resolved_logs'];
            }
            $log = [];
            $log['user'] = $caller;
            $log['at'] = time();
            $logs[] = $log;
            $updValues['resolved_logs'] = $logs;

            $his_resolvers = [];
            foreach ($logs as $v) {
                $his_resolvers[] = $v['user']['id'];
            }
            $updValues['his_resolvers'] = array_unique($his_resolvers);
        } elseif ($event_key === 'close_issue') {
            $updValues['closed_at'] = time();
            $updValues['closer'] = $caller;
        }
        if ($updValues) {
            $issue->data = array_replace($issue->data, $updValues);
            $issue->save();
        }
    }
}
