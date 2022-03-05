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

use App\Model\AccessBoardLog;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class AccessBoardLogDao extends Service
{
    /**
     * @return Collection<int, AccessBoardLog>
     */
    public function getByProjectKeyAndUserId(string $projectKey, int $userId): Collection
    {
        return AccessBoardLog::where('project_key', $projectKey)
            ->where('user_id', $userId)
            ->orderBy('latest_access_time', 'desc')
            ->get();
    }

    public function create(string $projectKey, int $boardId, int $userId, ?string $latestAccessTime = null): AccessBoardLog
    {
        $model = new AccessBoardLog();
        $model->project_key = $projectKey;
        $model->board_id = $boardId;
        $model->user_id = $userId;
        $model->latest_access_time = $latestAccessTime ?? time();
        $model->save();

        return $model;
    }

    public function firstByBoardIdAndUserId(string $projectKey, int $boardId, int $userId): AccessBoardLog
    {
        /** @var AccessBoardLog $model */
        $model = AccessBoardLog::query()->where('project_key', $projectKey)
            ->where('user_id', $userId)
            ->where('board_id', $boardId)
            ->first();

        if ($model) {
            $model->latest_access_time = time();
            $model->save();
        } else {
            $model = $this->create($projectKey, $boardId, $userId);
        }

        return $model;
    }
}
