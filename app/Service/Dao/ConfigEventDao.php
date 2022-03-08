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
use App\Model\ConfigEvent;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ConfigEventDao extends Service
{
    /**
     * @return Collection<int, ConfigEvent>
     */
    public function findByProjectKey(string $key)
    {
        return ConfigEvent::query()->where('project_key', ProjectConstant::SYS)
            ->orWhere('project_key', $key)
            ->orderBy('project_key')
            ->orderBy('id')
            ->get();
    }
}
