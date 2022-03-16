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
namespace App\Service;

use App\Constants\ProjectConstant;
use App\Model\ConfigField;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class FieldService extends Service
{
    /**
     * @return Collection<int, ConfigField>
     */
    public function getByProjectKey(string $projectKey, array $columns = ['*'])
    {
        return ConfigField::where('project_key', ProjectConstant::SYS)
            ->orWhere('project_key', $projectKey)
            ->orderBy('project_key', 'asc')
            ->orderBy('id', 'asc')
            ->get($columns);
    }
}
