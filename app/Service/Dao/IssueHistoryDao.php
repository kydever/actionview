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

use App\Model\IssueHistory;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class IssueHistoryDao extends Service
{
    /**
     * @return Collection<int, IssueHistory>
     */
    public function findMany(string $projectKey, int $issueId, string $direction = 'asc')
    {
        return IssueHistory::query()
            ->where('project_key', $projectKey)
            ->where('issue_id', $issueId)
            ->orderBy('id', $direction)
            ->get();
    }

    public function create(array $attributes): IssueHistory
    {
        $model = new IssueHistory();
        $model->project_key = $attributes['project_key'];
        $model->issue_id = $attributes['issue_id'];
        $model->operation = $attributes['operation'] ?? 'create';
        $model->operated_at = $attributes['operated_at'] ?? time();
        $model->operator = $attributes['operator'];
        $model->data = $attributes['data'] ?? [];
        $model->save();

        return $model;
    }
}
