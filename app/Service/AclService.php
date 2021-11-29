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

use App\Acl\Eloquent\Group;
use App\Acl\Eloquent\Roleactor;
use App\Constants\Permission;
use App\Constants\UserConstant;
use App\Model\Project;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\AclRoleactorDao;
use App\Service\Dao\AclRolePermissionDao;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;

class AclService extends Service
{
    #[Inject]
    protected AclGroupDao $group;

    public function getBoundGroups(int $userId): array
    {
        $groups = [];
        $models = $this->group->findByUserId($userId);
        foreach ($models as $group) {
            $groups[] = ['id' => $group->id, 'name' => $group->name];
        }
        return $groups;
    }

    public function hasAccess(int $userId, Project $project, string $access): bool
    {
        if (in_array($access, [Permission::PROJECT_VIEW, Permission::PROJECT_MANAGE]) && $project->isPrincipal($userId)) {
            return true;
        }

        $permissions = $this->getPermissionsFromContext($userId, $project);

        if ($access === Permission::PROJECT_VIEW) {
            return (bool) $permissions;
        }

        return in_array($access, $permissions);
    }

    public function getPermissionsFromContext(int $userId, Project $project): array
    {
        return Context::getOrSet('permission:' . $userId . ':' . $project->id, function () use ($userId, $project) {
            var_dump(123);
            return $this->getPermissions($userId, $project);
        });
    }

    #[Cacheable(prefix: 'permission', value: '#{userId}:#{project.id}', ttl: 120)]
    public function getPermissions(int $userId, Project $project): array
    {
        $groups = $this->getBoundGroups($userId);
        $groupIds = array_column($groups, 'id');

        $actors = di()->get(AclRoleactorDao::class)->findByProjectKey($project->key, $userId, $groupIds);
        $roleIds = $actors->columns('role_id')->toArray();
        $roleIds = array_values(array_unique($roleIds));

        $permissions = di()->get(AclRolePermissionDao::class)->findDictionaryByRoleIds($project->key, $roleIds);
        $defaultPermissions = di()->get(AclRolePermissionDao::class)->getDefaultPermissions();

        $result = [];
        foreach ($roleIds as $roleId) {
            if ($model = $permissions[$roleId] ?? null) {
                $result = array_merge($result, $model->permissions);
            } elseif ($permission = $defaultPermissions[$roleId] ?? null) {
                $result = array_merge($result, $permission);
            }
        }

        if ($project->isPrincipal($userId) || UserConstant::isSuperAdmin($userId)) {
            ! in_array('view_project', $result) && $result[] = 'view_project';
            ! in_array('manage_project', $result) && $result[] = 'manage_project';
        }

        if (! $project->isActive()) {
            $result = array_values(array_intersect($result, ['view_project', 'download_file']));
        }

        return array_values(array_unique($result));
    }

    public function getUserIdsByPermission($access, $key)
    {
        $permissions = di()->get(AclRolePermissionDao::class)->findByProject($key);
        $defaultPermissions = di()->get(AclRolePermissionDao::class)->getDefaultPermissions();

        $projectRoleIds = $permissions->columns('role_id')->toArray();
        $roleIds = [];
        foreach ($defaultPermissions as $roleId => $permission) {
            if (! in_array($roleId, $projectRoleIds) && in_array($access, $permission)) {
                $roleIds[] = $roleId;
            }
        }

        foreach ($permissions as $permission) {
            if ($permission->hasAccess($access)) {
                $roleIds[] = $permission->role_id;
            }
        }

        di()->get(AclRoleactorDao::class)->findByProjectKey();

        $user_ids = [];
        $group_ids = [];
        $role_actors = Roleactor::whereRaw(['project_key' => $project_key, 'role_id' => ['$in' => $role_ids]])->get();
        foreach ($role_actors as $actor) {
            if (isset($actor->user_ids) && $actor->user_ids) {
                $user_ids = array_merge($user_ids, $actor->user_ids);
            }
            if (isset($actor->group_ids) && $actor->group_ids) {
                $group_ids = array_merge($group_ids, $actor->group_ids);
            }
        }

        foreach ($group_ids as $group_id) {
            $group = Group::find($group_id);
            if ($group && isset($group->users) && $group->users) {
                $user_ids = array_merge($user_ids, $group->users);
            }
        }

        return array_values(array_unique($user_ids));
    }
}
