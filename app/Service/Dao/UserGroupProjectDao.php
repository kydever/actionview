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

use App\Model\UserGroupProject;
use Han\Utils\Service;

class UserGroupProjectDao extends Service
{
    /**
     * @return \Hyperf\Database\Model\Collection|UserGroupProject[]
     */
    public function findByUGIds(array $ids)
    {
        return UserGroupProject::query()->whereIn('ug_id', $ids)
            ->where('link_count', '>', 0)
            ->get();
    }

    public function findByGroupId(int $groupId)
    {
        return UserGroupProject::query()
            ->where('ug_id', $groupId)
            ->get();
    }

    public function firstByUserId(int $userId, string $key): ?UserGroupProject
    {
        return UserGroupProject::query()
            ->where('ug_id', $userId)
            ->where('project_key', $key)
            ->first();
    }

    /**
     * @return \Hyperf\Database\Model\Collection|UserGroupProject[]
     */
    public function findByProjectKey(string $key)
    {
        return UserGroupProject::query()->where('project_key', $key)
            ->where('link_count', '>', 0)
            ->get();
    }
}
