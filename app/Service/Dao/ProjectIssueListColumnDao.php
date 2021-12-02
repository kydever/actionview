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

use App\Model\ProjectIssueListColumn;
use Han\Utils\Service;

class ProjectIssueListColumnDao extends Service
{
    /**
     * @return null|object|ProjectIssueListColumn
     */
    public function getDisplayColumns(string $key)
    {
        return ProjectIssueListColumn::query()
            ->where('project_key', $key)
            ->first();
    }
}
