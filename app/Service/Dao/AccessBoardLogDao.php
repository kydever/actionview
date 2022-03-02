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
use App\Model\Project;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class AccessBoardLogDao extends Service
{
    public function getByProjectKeyAndUserId(Project $projectKey, int $userId): Collection
    {
        return AccessBoardLog::where('project_key', $projectKey)
            ->where('user_id', $userId)
            ->orderBy('latest_access_time', 'desc')
            ->get();
    }
}
