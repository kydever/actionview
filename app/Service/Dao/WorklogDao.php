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

    public function create(string $key, int $issueId, array $attributes): Worklog
    {
        $model = new Worklog();
        $model->project_key = $key;
        $model->issue_id = $issueId;
        $model->spend = $attributes['spend'];
        $model->spend_m = $attributes['spend_m'];
        $model->started_at = $attributes['started_at'];
        $model->adjust_type = $attributes['adjust_type'];
        $model->leave_estimate = $attributes['leave_estimate'] ?? '';
        $model->cut = $attributes['cut'] ?? '';
        $model->comments = $attributes['comments'] ?? '';
        $model->recorder = $attributes['recorder'];
        $model->recorded_at = time();
        $model->save();

        return $model;
    }

    /**
     * @return Collection<int, Worklog>
     */
    public function findManyProjectKeyAndIssueId(string $key, int $issueId)
    {
        return Worklog::query()
            ->where('project_key', $key)
            ->where('issue_id', $issueId)
            ->orderBy('recorded_at', 'desc')
            ->get();
    }
}
