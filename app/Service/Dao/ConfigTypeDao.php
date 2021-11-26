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
use App\Model\ConfigType;
use Han\Utils\Service;

class ConfigTypeDao extends Service
{
    /**
     * @return ConfigType[]|\Hyperf\Database\Model\Collection
     */
    public function findDefault()
    {
        return ConfigType::query()->where('project_key', ProjectConstant::SYS)->get();
    }
}
