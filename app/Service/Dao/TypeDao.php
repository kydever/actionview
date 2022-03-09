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

    public function findById(int $id): ?ConfigType
    {
        return ConfigType::findFromCache($id);
    }

    public function existsByNameOrAbb(string $name, string $abb): bool
    {
        return ConfigType::where('name', $name)
            ->orWhere('abb', $abb)
            ->exists();
    }

    public function existsByName(string $name): bool
    {
        return ConfigType::where('name', $name)
            ->exists();
    }

    public function existsByAbb(string $abb): bool
    {
        return ConfigType::where('abb', $abb)
            ->exists();
    }

    public function createOrUpdate(int $id, string $key, array $attributes): ConfigType
    {
        $model = $this->findById($id);
        if ($this->existsByName($attributes['name']) && $model?->name != $attributes['name']) {
            throw new BusinessException(ErrorCode::TYPE_NAME_ALREADY_EXIST);
        }
        if ($this->existsByAbb($attributes['abb']) && $model?->abb != $attributes['abb']) {
            throw new BusinessException(ErrorCode::TYPE_ABB_ALREADY_EXIST);
        }
        if (empty($model)) {
            $model = new ConfigType();
            $model->project_key = $key;
        }
        $model->sn = time();
        $model->name = $attributes['name'];
        $model->abb = $attributes['abb'];
        $model->screen_id = $attributes['screen_id'];
        $model->workflow_id = $attributes['workflow_id'];
        $model->description = $attributes['description'] ?? '';
        $model->save();

        return $model;
    }
}
