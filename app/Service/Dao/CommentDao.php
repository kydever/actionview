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

use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use App\Model\Comment;

class CommentDao extends Service
{
    /**
     * @param int $id
     * @param bool $isAsc
     * @retrun Collection<int, Comment>
     */
    public function findByIssueId(int $id, bool $isAsc = false)
    {
        return Comment::query()->where('issue_id', $id)
            ->orderBy('id', $isAsc ? 'asc': 'desc')
            ->get();
    }
}
