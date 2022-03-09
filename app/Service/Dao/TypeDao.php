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

use App\Model\ConfigType;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class TypeDao extends Service
{
    /**
     * @return Collection<int, ConfigType>
     */
    public function findByProjectKey(string $key)
    {
        return ConfigType::query()->where('project_key', $key)
            ->orderBy('sn')
            ->get();
    }
}
