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
}
