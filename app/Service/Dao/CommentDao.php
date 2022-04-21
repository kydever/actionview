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
use App\Model\Comment;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;
use Hyperf\Utils\Arr;

class CommentDao extends Service
{
    public function first(int $id, bool $throw = false): ?Comment
    {
        $model = Comment::findFromCache($id);
        if ($throw && empty($model)) {
            throw new BusinessException(ErrorCode::ISSUE_DONT_HAVE_COMMENTS);
        }
        return $model;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function findByIssueId(int $id, bool $isAsc = false)
    {
        return Comment::query()->where('issue_id', $id)
            ->orderBy('id', $isAsc ? 'asc' : 'desc')
            ->get();
    }

    public function countByIssueId(int $issueId)
    {
        return Comment::query()->where('issue_id', $issueId)->count();
    }

    public function deleteByIssueIds(array|int $issueIds)
    {
        $issueIds = Arr::wrap($issueIds);

        return Comment::whereIn('issue_id', $issueIds)->delete();
    }
}
