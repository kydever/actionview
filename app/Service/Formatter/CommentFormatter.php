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
namespace App\Service\Formatter;

use App\Model\Comment;
use Han\Utils\Service;

class CommentFormatter extends Service
{
    public function base(Comment $model): array
    {
        return [
            'id' => $model->id,
            'issue_id' => $model->issue_id,
            'contents' => $model->contents,
            'atWho' => $model->at_who,
            'creator' => $model->creator,
            'created_at' => $model->created_at->getTimestamp(),
        ];
    }
}
