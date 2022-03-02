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

use App\Model\Sprint;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class SprintDao extends Service
{
    /**
     * @return \Hyperf\Database\Model\Collection|Sprint[]
     */
    public function getSprintList(string $key)
    {
        return Sprint::query()->where('project_key', $key)
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('no', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, Sprint>
     */
    public function getByProjectKeyAndStatus(string $projectKey): Collection
    {
        return Sprint::where('project_key', $projectKey)
            ->whereIn('status', ['active', 'waiting'])
            ->orderBy('no', 'asc')
            ->get();
    }

    public function maxByProjectKeyAndStatus(string $projectKey)
    {
        return Sprint::where('project_key', $projectKey)
            ->where('status', 'completed')
            ->max('no');
    }
}
