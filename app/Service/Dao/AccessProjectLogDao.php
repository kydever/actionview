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

class AccessProjectLogDao extends Service
{
    public function latest(int $userId): ?AccessProjectLog
    {
        $datetime = Carbon::now()->subDays(14);
        return AccessProjectLog::query()->where('user_id', $userId)
            ->where('latest_access_time', '>', $datetime->getTimestamp())
            ->orderBy('latest_access_time', 'desc')
            ->first();
    }
}
