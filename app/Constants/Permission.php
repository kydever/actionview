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
namespace App\Constants;

use App\Acl\Permissions;

class Permission
{
    public const SYS_ADMIN = 'sys_admin';

    public const VIEW_PROJECT = 'view_project';

    public const MANAGE_PROJECT = 'manage_project';

    public const ASSIGNED_ISSUE = 'assigned_issue';

    public const ASSIGN_ISSUE = 'assign_issue';

    public const CREATE_ISSUE = 'create_issue';

    public const EDIT_ISSUE = 'edit_issue';

    public const EDIT_SELF_ISSUE = 'edit_self_issue';

    public const DELETE_ISSUE = 'delete_issue';

    public const DELETE_SELF_ISSUE = 'delete_self_issue';

    public const LINK_ISSUE = 'link_issue';

    public const MOVE_ISSUE = 'move_issue';

    public const RESOLVE_ISSUE = 'resolve_issue';

    public const RESET_ISSUE = 'reset_issue';

    public const CLOSE_ISSUE = 'close_issue';

    public const VIEW_WORKFLOW = 'view_workflow';

    public const EXEC_WORKFLOW = 'exec_workflow';

    public const UPLOAD_FILE = 'upload_file';

    public const DOWNLOAD_FILE = 'download_file';

    public const REMOVE_FILE = 'remove_file';

    public const REMOVE_SELF_FILE = 'remove_self_file';

    public const ADD_COMMNETS = 'add_comments';

    public const EDIT_COMMNETS = 'edit_comments';

    public const EDIT_SELF_COMMNETS = 'edit_self_comments';

    public const DELETE_COMMNETS = 'delete_comments';

    public const DELETE_SELF_COMMNETS = 'delete_self_comments';

    public const ADD_WORKLOG = 'add_worklog';

    public const EDIT_WORKLOG = 'edit_worklog';

    public const EDIT_SELF_WORKLOG = 'edit_self_worklog';

    public const DELETE_WORKLOG = 'delete_worklog';

    public const DELETE_SELF_WORKLOG = 'delete_self_worklog';

    /**
     * Return an object representing all actions.
     *
     * @return Permissions
     */
    public static function all()
    {
        return [
            static::VIEW_PROJECT,
            static::MANAGE_PROJECT,

            static::ASSIGNED_ISSUE,
            static::ASSIGN_ISSUE,

            static::CREATE_ISSUE,
            static::EDIT_ISSUE,
            static::EDIT_SELF_ISSUE,
            static::DELETE_ISSUE,
            static::DELETE_SELF_ISSUE,
            static::LINK_ISSUE,
            static::MOVE_ISSUE,
            static::RESOLVE_ISSUE,
            static::RESET_ISSUE,
            static::CLOSE_ISSUE,

            // static::VIEW_WORKFLOW,
            static::EXEC_WORKFLOW,

            static::UPLOAD_FILE,
            static::DOWNLOAD_FILE,
            static::REMOVE_FILE,
            static::REMOVE_SELF_FILE,

            static::ADD_COMMNETS,
            static::EDIT_COMMNETS,
            static::EDIT_SELF_COMMNETS,
            static::DELETE_COMMNETS,
            static::DELETE_SELF_COMMNETS,

            static::ADD_WORKLOG,
            static::EDIT_WORKLOG,
            static::EDIT_SELF_WORKLOG,
            static::DELETE_WORKLOG,
            static::DELETE_SELF_WORKLOG,
        ];
    }
}
