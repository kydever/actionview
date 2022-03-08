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
use App\Model\ConfigResolution;
use Han\Utils\Service;

class ConfigResolutionDao extends Service
{
    /**
     * @return ConfigResolution[]|\Hyperf\Database\Model\Collection
     */
    public function findOrByProjectKey(string $key)
    {
        return ConfigResolution::query()->where('project_key', ProjectConstant::SYS)
            ->orWhere('project_key', $key)
            ->orderBy('project_key')
            ->orderBy('sn')
            ->get();
    }
}
