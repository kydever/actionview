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

use App\Constants\ProjectConstant;
use App\Model\ConfigPriority;
use Han\Utils\Service;

class ConfigPriorityDao extends Service
{
    public function findOrByProjectKey(string $key)
    {
        return ConfigPriority::query()->where('project_key', ProjectConstant::SYS)
            ->orWhere('project_key', $key)
            ->orderBy('project_key')
            ->orderBy('sn')
            ->get();
    }
}
