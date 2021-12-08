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

use App\Model\ConfigState;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ConfigStateDao extends Service
{
    /**
     * @return Collection<int, ConfigState>
     */
    public function findOrByProjectKey(string $key)
    {
        return ConfigState::query()->where('project_key', '$_sys_$')
            ->orWhere('project_key', $key)
            ->orderBy('project_key', )
            ->orderBy('sn', )
            ->get();
    }
}
