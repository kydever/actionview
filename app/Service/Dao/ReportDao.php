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

use App\Model\Report;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ReportDao extends Service
{
    /**
     * @return Collection<int, Report>
     */
    public function getByProjectKey(string $key, int $userId)
    {
        return Report::where('project_key', $key)
            ->where('user', $userId)
            ->get();
    }

    /**
     * @return Collection<int, Report>
     */
    public function get(string $key, string $mode, int $userId)
    {
        return Report::query()
            ->where('project_key', $key)
            ->where('mode', $mode)
            ->where('user', $userId)
            ->get();
    }

    public function deleteByIds(array $ids): int
    {
        return Report::query()
            ->whereIn('id', $ids)
            ->delete();
    }
}
