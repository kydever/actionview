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

use App\Constants\UserConstant;
use App\Model\Project;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\AclRoleactorDao;
use App\Service\Dao\AclRolePermissionDao;
use Han\Utils\Service;
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
}
