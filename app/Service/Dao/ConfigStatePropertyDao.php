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

use App\Model\ConfigStateProperty;
use Han\Utils\Service;

class ConfigStatePropertyDao extends Service
{
    public function firstByProjectKey(string $key): ?ConfigStateProperty
    {
        return ConfigStateProperty::query()->where('project_key', $key)->first();
    }
}
