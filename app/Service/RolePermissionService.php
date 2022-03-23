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

use App\Model\AclRolePermission;
use Han\Utils\Service;

class RolePermissionService extends Service
{
    public function create(array $attributes): AclRolePermission
    {
        $model = new AclRolePermission();
        $model->project_key = $attributes['project_key'];
        $model->role_id = $attributes['role_id'];
        $model->permissions = $attributes['permissions'];
        $model->save();

        return $model;
    }
}
