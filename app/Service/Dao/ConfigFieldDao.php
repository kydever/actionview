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

use App\Model\ConfigField;
use Han\Utils\Service;

class ConfigFieldDao extends Service
{
    /**
     * @return ConfigField[]|\Hyperf\Database\Model\Collection
     */
    public function getFieldList(string $key)
    {
        return ConfigField::query()->where('project_key', '$_sys_$')
            ->orWhere('project_key', $key)
            ->orderBy('project_key')
            ->orderBy('id')
            ->get();
    }
}
