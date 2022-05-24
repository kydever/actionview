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

use App\Model\Activity;
use Carbon\Carbon;
use Han\Utils\Service;
use Hyperf\Database\Model\Builder;

class ActivityDao extends Service
{
    public function getQuery(array $keys): Builder
    {
        return Activity::query()->whereIn('project_key', $keys);
    }

    public function recentCountGroupByProjectKeys(array $keys, int $days)
    {
        $datetime = Carbon::now()->subDays($days)->toDateTimeString();

        $query = $this->getQuery($keys)->where('created_at', '>=', $datetime);

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

    public function whereBy(string $column, string $value): Builder
    {
        return Activity::query()
            ->where($column, $value);
    }
}
