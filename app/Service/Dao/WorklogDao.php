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
use App\Exception\BusinessException;
use App\Model\Worklog;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class WorklogDao extends Service
{
    /**
     * @return Collection<int, Worklog>
     */
    public function findByIssueId(string $key, int $issueId, string $sortable = 'desc')
    {
        return Worklog::where('project_key', $key)
            ->where('issue_id', $issueId)
            ->orderBy('recorded_at', $sortable)
            ->get();
    }

    public function findById(int $id, bool $throw = false): ?Worklog
    {
        $model = Worklog::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::WORKLOG_NOT_FOUND);
        }

        return $model;
    }
}
