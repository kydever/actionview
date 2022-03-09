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

class TypeDao extends Service
{
    public function getByProjectKeyOrderSnOldest(string $projectKey)
    {
        return ConfigType::where('project_key', $projectKey)
            ->orderBy('sn', 'asc')
            ->get();
    }
}
