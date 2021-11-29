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

class Permission
{
    public const SYS_ADMIN = 'sys_admin';

    public const PROJECT_VIEW = 'view_project';

    public const PROJECT_MANAGE = 'manage_project';

    public const ISSUE_ASSIGNED = 'assigned_issue';
}
