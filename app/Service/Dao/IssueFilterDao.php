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

use App\Model\IssueFilter;
use Han\Utils\Service;

class IssueFilterDao extends Service
{
    /**
     * @return \Hyperf\Database\Model\Collection|IssueFilter[]
     */
    public function getIssueFilters(string $key, int $userId)
    {
        return IssueFilter::query()
            ->where('project_key', $key)
            ->where(function ($query) use ($userId) {
                $query->where('creator->$.id', $userId)
                    ->orWhere('scope', 'public');
            })
            ->get();
    }
}
