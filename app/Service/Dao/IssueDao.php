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
use App\Constants\StatusConstant;
use App\Exception\BusinessException;
use App\Model\Issue;
use Han\Utils\Service;
use Hyperf\Database\Model\Builder;

class IssueDao extends Service
{
    public function first(int $id, bool $throw = false): ?Issue
    {
        $model = Issue::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, 'ISSUE 不存在');
        }

        return $model;
    }

    public function getQuery(array $keys): Builder
    {
        return Issue::query()->whereIn('project_key', $keys);
    }

    public function count(string $projectKey): int
    {
        return Issue::query()->where('project_key', $projectKey)->count();
    }

    public function countGroupByProjectKeys(array $keys, string $resolution = '', int $userId = 0): array
    {
        $query = $this->getQuery($keys)
            ->where('del_flg', '<>', StatusConstant::DELETED);
        if (! empty($resolution)) {
            $query->where('resolution', $resolution);
        }

        if ($userId > 0) {
            $query->where('assignee->id', $userId);
        }

        $items = $query->groupBy('project_key')
            ->selectRaw('COUNT(0) as `cnt`, project_key')
            ->get()
            ->toArray();

        $result = [];
        foreach ($items as $item) {
            $result[$item['project_key']] = $item['cnt'];
        }

        return $result;
    }
}
