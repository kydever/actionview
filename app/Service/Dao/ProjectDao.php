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

use App\Model\Project;
use Han\Utils\Service;

class ProjectDao extends Service
{
    public function firstByKey(string $key): ?Project
    {
        return Project::query()->where('key', $key)->first();
    }

    /**
     * @return \Hyperf\Database\Model\Collection|Project[]
     */
    public function findByKeys(array $keys)
    {
        return Project::query()->whereIn('key', $keys)->get();
    }
}
