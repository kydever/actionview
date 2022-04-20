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
namespace App\Service\Dao;

use App\Model\AclRoleactor;
use Han\Utils\Service;
use Hyperf\Database\Model\Builder;

class AclRoleactorDao extends Service
{
    /**
     * @return AclRoleactor[]|\Hyperf\Database\Model\Collection
     */
    public function findByGroupId(int $groupId)
    {
        return AclRoleactor::query()->whereJsonContains('group_ids', $groupId)->get();
    }

    /**
     * @return AclRoleactor[]|\Hyperf\Database\Model\Collection
     */
    public function findByProjectKey(string $projectKey, int $userId = 0, array $orGroupIds = [])
    {
        $query = AclRoleactor::query()->where('project_key', $projectKey);
        if (! empty($userId) || ! empty($orGroupIds)) {
            $query->where(static function (Builder $query) use ($userId, $orGroupIds) {
                if ($userId > 0) {
                    $query->orWhereJsonContains('user_ids', $userId);
                }

                foreach ($orGroupIds as $groupId) {
                    $query->orWhereJsonContains('group_ids', $groupId);
                }
            });
        }

        return $query->get();
    }

    /**
     * @return AclRoleactor[]|\Hyperf\Database\Model\Collection
     */
    public function findByRoleIds(string $projectKey, array $roleIds)
    {
        return AclRoleactor::query()->where('project_key', $projectKey)
            ->whereIn('role_id', $roleIds)
            ->get();
    }

    public function firstByRoleId(string $projectKey, int $roleId): ?AclRoleactor
    {
        return AclRoleactor::query()->where('project_key', $projectKey)
            ->where('role_id', $roleId)
            ->first();
    }

    public function createNewRoleactor(array $attributes): AclRoleactor
    {
        return AclRoleactor::create($attributes);
    }
}
