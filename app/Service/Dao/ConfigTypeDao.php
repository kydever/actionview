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
use App\Model\ConfigType;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ConfigTypeDao extends Service
{
    /**
     * @return ConfigType[]|\Hyperf\Database\Model\Collection
     */
    public function findDefault()
    {
        return ConfigType::query()->where('project_key', ProjectConstant::SYS)->get();
    }

    public function first(int $id, bool $throw = false): ?ConfigType
    {
        $model = ConfigType::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::ISSUE_TYPE_NOT_EMPTY);
        }
        return $model;
    }

    /**
     * @return Collection<int, ConfigType>
     */
    public function getTypeList(string $key, array $with = [])
    {
        $query = ConfigType::query();
        if ($with) {
            $query->with(...$with);
        }
        return $query->where('project_key', $key)->orderBy('sn')->get();
    }

    public function existsByWorkFlowId(int $workflowId): bool
    {
        return ConfigType::where('workflow_id', $workflowId)->exists();
    }

    /**
     * @return Collection<int, ConfigType>
     */
    public function findByWorkflowIds(array $workflowIds)
    {
        return ConfigType::query()->whereIn('workflow_id', $workflowIds)->get();
    }
}
