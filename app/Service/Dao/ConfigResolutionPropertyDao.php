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

use App\Model\ConfigResolutionProperty;
use Han\Utils\Service;

class ConfigResolutionPropertyDao extends Service
{
    public function firstByProjectKey(string $key): ?ConfigResolutionProperty
    {
        return ConfigResolutionProperty::query()->where('project_key', $key)->first();
    }
}
