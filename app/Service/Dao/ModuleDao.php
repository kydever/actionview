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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Module;
use Han\Utils\Service;

class ModuleDao extends Service
{
    public function first(int $id, bool $throw = false): ?Module
    {
        $model = Module::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::SERVER_ERROR);
        }

        return $model;
    }

    /**
     * @return \Hyperf\Database\Model\Collection|Module[]
     */
    public function getModuleList(string $key)
    {
        return Module::query()->where('project_key', $key)->orderBy('sn')->get();
    }
}
