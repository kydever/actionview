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

use App\Model\AccessProjectLog;
use Carbon\Carbon;
use Han\Utils\Service;
use function Han\Utils\sort;

class AccessProjectLogDao extends Service
{
    public function create(string $projectKey, int $userId): void
    {
        AccessProjectLog::query()->where('project_key', $projectKey)
            ->where('user_id', $userId)
            ->delete();
        AccessProjectLog::query()->create(['project_key' => $projectKey, 'user_id' => $userId, 'latest_access_time' => time()]);
    }

    public function latest(int $userId): ?AccessProjectLog
    {
        $datetime = Carbon::now()->subDays(14);
        return AccessProjectLog::query()->where('user_id', $userId)
            ->where('latest_access_time', '>', $datetime->getTimestamp())
            ->orderBy('latest_access_time', 'desc')
            ->first();
    }

    public function findLatestProjectKeys(int $userId): array
    {
        $models = AccessProjectLog::query()->where('user_id', $userId)
            ->selectRaw('`project_key`, max(`latest_access_time`) as `latest_access_time`')
            ->groupBy('project_key')
            ->get()
            ->columns(['project_key', 'latest_access_time'])
            ->toArray();

        $result = sort($models, function ($data) {
            return $data['latest_access_time'] ?? 0;
        });

        return array_column($result->toArray(), 'project_key');
    }
}
