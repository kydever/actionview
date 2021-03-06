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

use App\Model\UserIssueFilter;
use Han\Utils\Service;

class UserIssueFilterDao extends Service
{
    public function getUserFilter(string $key, int $userId): ?UserIssueFilter
    {
        return UserIssueFilter::query()
            ->where('project_key', $key)
            ->where('user->id', $userId)
            ->first();
    }
}
