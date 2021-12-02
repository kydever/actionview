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

use App\Model\UserIssueListColumn;
use Han\Utils\Service;

class UserIssueListColumnDao extends Service
{
    /**
     * @return null|object|UserIssueListColumn
     */
    public function getUserDisplayColumns(string $key, int $userId)
    {
        return UserIssueListColumn::query()
            ->where('project_key', $key)
            ->where('user->id', $userId)
            ->first();
    }
}
