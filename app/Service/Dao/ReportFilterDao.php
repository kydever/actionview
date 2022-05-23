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

use App\Model\ReportFilter;
use Han\Utils\Service;

class ReportFilterDao extends Service
{
    public function first(string $projectKey, string $mode, int $userId, bool $throw = false)
    {
        return ReportFilter::query()
            ->where('project_key', $projectKey)
            ->where('mode', $mode)
            ->where('user_id', $userId)
            ->first();
    }
}
