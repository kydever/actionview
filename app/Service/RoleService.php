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

use App\Acl\Eloquent\RolePermissions;
use App\Model\AclRoleactor;
use App\Model\Project;
use App\Service\Context\RoleactorContext;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\AclRoleactorDao;
use App\Service\Dao\AclRoleDao;
use App\Service\Dao\AclRolePermissionDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\GroupFormatter;
use App\Service\Formatter\RoleFormatter;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class RoleService extends Service
{
    #[Inject]
    protected AclRoleDao $dao;

    #[Inject]
    protected ProviderService $provider;

    public function index(Project $project)
    {
        $roles = $this->dao->findOrByProjectKey($project->key);
        $roleIds = $roles->columns('id')->toArray();

        $actorContext = RoleactorContext::getInstance($project->key)->init($roleIds);
        $permissions = di()->get(AclRolePermissionDao::class)->findDictionaryByRoleIds($project->key, $roleIds);
        $defaultPermissions = di()->get(AclRolePermissionDao::class)->getDefaultPermissions();

        $result = [];
        foreach ($roles as $role) {
            $item = di()->get(RoleFormatter::class)->base($role);
            if ($project->isSYS()) {
                /** @var null|AclRoleactor $actor */
                $actor = $actorContext->first($role->id);
                if ($actor?->user_ids || $actor?->group_ids) {
                    $item['is_used'] = true;
                }
            } else {
                $groups = $this->getGroupsAndUsers($project->key, $role->id);
                $item['users'] = $groups['users'];
                $item['groups'] = $groups['groups'];
            }

            $item['permissions'] = $permissions[$role->id]?->permissions ?? $defaultPermissions[$role->id] ?? [];
            $result[] = $item;
        }

        return $result;
    }

    public function setPermissions(Project $project, int $roleId){

    }

    public function getGroupsAndUsers(string $projectKey, int $roleId): array
    {
        $actor = di()->get(AclRoleactorDao::class)->firstByRoleId($projectKey, $roleId);
        if (! $actor) {
            return ['users' => [], 'groups' => []];
        }

        $users = [];
        $groups = [];
        if ($userIds = $actor?->user_ids) {
            $models = di()->get(UserDao::class)->findMany($userIds);
            $users = di()->get(UserFormatter::class)->formatSmalls($models);
        }

        if ($groupIds = $actor?->group_ids) {
            $models = di()->get(AclGroupDao::class)->findMany($groupIds);
            $groups = di()->get(GroupFormatter::class)->formatList($models);
        }

        return ['users' => $users, 'groups' => $groups];
    }
}
