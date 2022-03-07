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
use App\Constants\ProjectConstant;
use App\Exception\BusinessException;
use App\Model\ConfigState;
use Han\Utils\Service;
use Hyperf\Database\Model\Builder;
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
            ->orderBy('project_key', 'asc')
            ->orderBy('sn', 'asc')
            ->get();
    }

    public function findById(int $id): ?ConfigState
    {
        return ConfigState::findFromCache($id);
    }

    public function createOrUpdate(int $id, string $projectKey, array $attributes): ConfigState
    {
        $model = $this->findById($id);
        $name = $attributes['name'];
        if ($this->isStateExisted($projectKey, $name) && $model?->name != $name) {
            throw new BusinessException(ErrorCode::STATE_NAME_ALREADY_EXISTS);
        }
        if (empty($model)) {
            $model = new ConfigState();
            $model->project_key = $projectKey;
        }
        $model->name = $attributes['name'];
        $model->sn = time();
        $model->category = $attributes['category'];
        $model->save();

        return $model;
    }

    protected function isStateExisted(string $projectKey, string $name): bool
    {
        return ConfigState::query()->where(static function (Builder $builder) use ($projectKey) {
            $builder->where('project_key', ProjectConstant::SYS)
                ->orWhere('project_key', $projectKey);
        })->where('name', $name)
            ->exists();
    }
}
