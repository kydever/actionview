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

    public function findById(int $id, bool $throw = false): ?ConfigType
    {
        $model = ConfigType::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::TYPE_NOT_FOUND);
        }

        return $model;
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
        if (isset($attributes['name']) && $this->existsByName($attributes['name']) && $model?->name != $attributes['name']) {
            throw new BusinessException(ErrorCode::TYPE_NAME_ALREADY_EXIST);
        }
        if (isset($attributes['abb']) && $this->existsByAbb($attributes['abb']) && $model?->abb != $attributes['abb']) {
            throw new BusinessException(ErrorCode::TYPE_ABB_ALREADY_EXIST);
        }
        if (empty($model)) {
            $model = new ConfigType();
            $model->project_key = $key;
            $model->sn = time();
        }
        $model->name = $attributes['name'] ?? $model->name;
        $model->abb = $attributes['abb'] ?? $model->abb;
        $model->screen_id = $attributes['screen_id'] ?? $model->screen_id;
        $model->workflow_id = $attributes['workflow_id'] ?? $model->workflow_id;
        if (isset($attributes['type'])) {
            $model->type = $attributes['type'];
        }
        if (isset($attributes['description'])) {
            $model->description = $attributes['description'];
        }

        $disabled = $attributes['disabled'] ?? null;
        if ($disabled !== null) {
            $model->disabled = $disabled;
            if ($disabled) {
                $model->default = 0;
            }
        }
        $model->save();

        return $model;
    }

    public function delete(int $id): ConfigType
    {
        $model = $this->findById($id, true);
        $model->delete();

        return $model;
    }

    /**
     * @return Collection<int, ConfigType>
     */
    public function getByProjectKey(string $key)
    {
        return ConfigType::where('project_key', $key)->get();
    }

    public function findByIdAndProjectKey(int $id, string $key): ConfigType
    {
        $model = $this->findById($id, true);
        if ($model->project_key != $key) {
            throw new BusinessException(ErrorCode::TYPE_NOT_FOUND);
        }

        return $model;
    }

    public function sortable(string $key, array $attributes): array
    {
        if (isset($attributes['sequence'])) {
            $i = 1;
            foreach ($attributes['sequence'] as $modelId) {
                $model = $this->findById($modelId);
                if (empty($model) || $model->project_key != $key) {
                    continue;
                }
                $model->sn = $i++;
                $model->save();
            }
        }
        if (isset($attributes['defaultValue'])) {
            $this->findByIdAndProjectKey((int) $attributes['defaultValue'], $key);
            $models = $this->getByProjectKey($key);
            foreach ($models as $model) {
                if ($model->id == $attributes['defaultValue']) {
                    $model->default = true;
                    $model->save();
                } elseif (isset($model->default)) {
                    $model->default = false;
                    $model->save();
                }
            }
        }

        $sequence = $attributes['sequence'] ?? null;
        $default = $attributes['defaultValue'] ?? null;

        return [$sequence, $default];
    }
}
