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

use App\Model\Comment;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class CommentDao extends Service
{
    /**
     * @return Collection<int, Comment>
     */
    public function findByIssueId(int $id, bool $isAsc = false)
    {
        return Comment::query()->where('issue_id', $id)
            ->orderBy('id', $isAsc ? 'asc' : 'desc')
            ->get();
    }
}
