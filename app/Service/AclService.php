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

use App\Constants\Permission;
use App\Constants\UserConstant;
use App\Model\Project;
use App\Service\Context\GroupContext;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\AclRoleactorDao;
use App\Service\Dao\AclRolePermissionDao;
use Han\Utils\Service;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Annotation\Inject;

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
        if (in_array($access, [Permission::VIEW_PROJECT, Permission::MANAGE_PROJECT]) && $project->isPrincipal($userId)) {
            return true;
        }

        $permissions = $this->getPermissionsFromContext($userId, $project);

        if ($access === Permission::VIEW_PROJECT) {
            return (bool) $permissions;
        }

        return in_array($access, $permissions);
    }

    #[Cacheable(prefix: 'permission', value: '#{userId}:#{project.id}', group: 'context')]
    public function getPermissionsFromContext(int $userId, Project $project): array
    {
        return $this->getPermissions($userId, $project);
    }

    #[Cacheable(prefix: 'permission', value: '#{userId}:#{project.id}', ttl: 120)]
    public function getPermissions(int $userId, Project $project): array
    {
        if (UserConstant::isSuperAdmin($userId)) {
            return Permission::all();
        }

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

        if ($project->isPrincipal($userId)) {
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

        $userIds = [];
        $groupIds = [];
        $actors = di()->get(AclRoleactorDao::class)->findByRoleIds($key, $roleIds);

        foreach ($actors as $actor) {
            $userIds = array_merge($userIds, $actor->user_ids ?? []);
            $groupIds = array_merge($groupIds, $actor->group_ids ?? []);
        }

        $groups = GroupContext::instance()->find($groupIds);
        foreach ($groups as $group) {
            $userIds = array_merge($userIds, $group->users ?? []);
        }

        return array_values(array_unique($userIds));
    }

    public function isAllowed(int $userId, string $permission, Project $project)
    {
        $permissions = $this->getPermissionsFromContext($userId, $project);
        if ($permission == 'view_project') {
            return (bool) $permissions;
        }

        return in_array($permission, $permissions);
    }
}
