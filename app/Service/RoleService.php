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
use App\Constants\ProjectConstant;
use App\Event\AddUserToRoleEvent;
use App\Event\DelUserFromRoleEvent;
use App\Exception\BusinessException;
use App\Model\AclRole;
use App\Model\AclRoleactor;
use App\Model\AclRolePermission;
use App\Model\Project;
use App\Model\User;
use App\Service\Context\RoleactorContext;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\AclRoleactorDao;
use App\Service\Dao\AclRoleDao;
use App\Service\Dao\AclRolePermissionDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\AclRoleactorFormatter;
use App\Service\Formatter\GroupFormatter;
use App\Service\Formatter\RoleFormatter;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;

class RoleService extends Service
{
    #[Inject]
    protected AclRoleDao $dao;

    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected RoleFormatter $formatter;

    #[Inject]
    protected AclService $acl;

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

    public function create(array $attributes): array
    {
        $project = get_project();
        $user = get_user();
        $permissions = $attributes['permissions'] ?? null;
        if (! is_null($permissions)) {
            $allPermissions = $this->acl->getPermissions($user->id, $project);
            if (array_diff($permissions, $allPermissions)) {
                throw new BusinessException(ErrorCode::ROLE_INVALID);
            }
        }

        $model = new AclRole();
        $model->project_key = $project->key;
        $model->name = $attributes['name'];
        $model->save();

        if (! is_null($permissions) && $model) {
            di()->get(RolePermissionService::class)->create(['project_key' => $project->key, 'role_id' => $model->id, 'permissions' => $permissions]);
        }

        return $this->formatter->base($model);
    }

    public function update(int $id, array $attributes): array
    {
        $project = get_project();
        $user = get_user();
        $model = $this->dao->first($id, true);
        if ($project->key != $model->project_key) {
            throw new BusinessException(ErrorCode::ROLE_NOT_EXISTS);
        }
        $permissions = $attributes['permissions'] ?? null;
        if (! is_null($permissions)) {
            $allPermissions = $this->acl->getPermissions($user->id, $project);
            if (array_diff($permissions, $allPermissions)) {
                throw new BusinessException(ErrorCode::ROLE_INVALID);
            }
        }
        $model->name = $attributes['name'];
        $model->save();

        return $this->formatter->base($model);
    }

    public function delete(int $id): array
    {
        $project = get_project();
        $model = $this->dao->first($id, true);
        if ($project->key === ProjectConstant::SYS) {
            $actors = di()->get(AclRoleactorService::class)->getByRoleId($model->id);
            foreach ($actors as $actor) {
                $item = di(AclRoleactorFormatter::class)->base($actor);
                if ($item['user_ids'] || $item['group_ids']) {
                    throw new BusinessException(ErrorCode::ROLE_IS_USED);
                }
                $actor->delete();
            }
        } else {
            $actor = di()->get(AclRoleactorDao::class)->firstByRoleId($project->key, $model->id);
            if ($actor) {
                $actor->delete();
            }
        }
        $model->delete();

        return [
            'id' => $model->id,
        ];
    }

    /**
     * @param $input = [
     *     'users' => []
     * ]
     */
    public function setActor(array $input, int $id, Project $project)
    {
        $userIds = $input['users'] ?? null;
        $role = $this->dao->first($id, true);

        if (isset($userIds)) {
            $actor = di()->get(AclRoleactorDao::class)->firstByRoleId($project->key, $role->id);
            $oldUserIds = $actor?->user_ids ?? [];

            if (empty($actor)) {
                $actor = new AclRoleactor();
                $actor->project_key = $project->key;
                $actor->role_id = $role->id;
                $actor->group_ids = [];
            }

            $actor->user_ids = $userIds;
            $actor->save();

            di()->get(EventDispatcherInterface::class)->dispatch(new AddUserToRoleEvent(array_diff($userIds, $oldUserIds), $project->key));
            di()->get(EventDispatcherInterface::class)->dispatch(new DelUserFromRoleEvent(array_diff($oldUserIds, $userIds), $project->key));
        }

        $groups = $this->getGroupsAndUsers($project->key, $role->id);
        $result = $this->formatter->base($role);
        $result['users'] = $groups['users'];
        $result['groups'] = $groups['groups'];

        return $result;
    }

    /**
     * @param $input = [
     *     'permissions' => [],
     * ]
     */
    public function setPermissions(array $input, int $roleId, Project $project, User $user)
    {
        $permissions = $input['permissions'] ?? null;
        $role = $this->dao->first($roleId, true);
        if ($role->project_key != ProjectConstant::SYS && $role->project_key !== $project->key) {
            throw new BusinessException(ErrorCode::ROLE_NOT_EXISTS);
        }

        if (isset($permissions)) {
            $allPermissions = Permission::all();
            if (array_diff($permissions, $allPermissions)) {
                throw new BusinessException(ErrorCode::ROLE_INVALID);
            }

            if (! $user->mustContainsAccesses([Permission::MANAGE_PROJECT])) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }

            $model = di()->get(AclRolePermissionDao::class)->firstByProjectRoleId($project->key, $roleId);
            if (empty($model)) {
                $model = new AclRolePermission();
                $model->project_key = $project->key;
                $model->role_id = $roleId;
            }

            $model->permissions = $permissions;
            $model->save();
        }

        $result = di()->get(RoleFormatter::class)->base($role);
        $result['permissions'] = $permissions;
        return $result;
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
