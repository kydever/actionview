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
namespace App\Service;

use App\Constants\ErrorCode;
use App\Constants\Permission;
use App\Constants\StatusConstant;
use App\Exception\BusinessException;
use App\Model\Comment;
use App\Model\Project;
use App\Model\User;
use App\Service\Dao\CommentDao;
use App\Service\Formatter\CommentFormatter;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;

class CommentService extends Service
{
    #[Inject]
    protected CommentFormatter $formatter;

    #[Inject]
    protected AclService $acl;

    #[Inject]
    protected CommentDao $dao;

    public function index(int $id, bool $isAsc = false): array
    {
        $models = $this->dao->findByIssueId($id, $isAsc);

        return $this->formatter->formatList($models);
    }

    public function store(int $id, User $user, Project $project, array $input)
    {
        if (! $this->acl->isAllowed($user->id, Permission::ADD_COMMNETS, $project)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $contents = $input['contents'];
        $creator = ['id' => $user->id, 'name' => $user->first_name, 'email' => $user->email];

        $model = new Comment();
        $model->contents = $contents;
        $model->at_who = $input['atWho'] ?? [];
        $model->issue_id = $id;
        $model->creator = $creator;
        $model->save();

        return $this->formatter->base($model);
    }

    public function update(int $id, int $commentId, User $user, Project $project, array $input)
    {
        $model = $this->dao->first($commentId, true);
        if ($model->issue_id !== $id) {
            throw new BusinessException(ErrorCode::SERVER_ERROR);
        }

        $operation = $input['operation'] ?? null;
        $contents = $input['contents'] ?? null;
        if (isset($contents) && ! $contents) {
            throw new BusinessException(ErrorCode::ISSUE_COMMENT_CONTENTS_NOT_EXIST);
        }

        $creator = di()->get(UserFormatter::class)->tiny($user);

        if (isset($operation)) {
            $replies = $model->reply ?? [];

            switch ($operation) {
                case 'addReply':
                    if (! $this->acl->isAllowed($user->id, Permission::ADD_COMMNETS, $project)) {
                        throw new BusinessException(ErrorCode::PERMISSION_DENIED);
                    }

                    $replyId = md5(microtime(true) . $user->id);
                    $replies[] = Arr::only($input, ['contents', 'atWho']) + ['id' => $replyId, 'creator' => $creator, 'created_at' => time()];
                    $model->reply = $replies;
                    break;
                case 'editReply':
                    $replyId = $input['reply_id'] ?? null;
                    if (! $replyId) {
                        throw new BusinessException(ErrorCode::ISSUE_COMMENT_REPLY_ID_NOT_EXIST);
                    }

                    $replies = $model->reply;
                    $key = collect($replies)->search(static function ($item) use ($replyId) {
                        return $item['id'] == $replyId;
                    });
                    if (! $key) {
                        throw new BusinessException(ErrorCode::ISSUE_COMMENT_REPLY_NOT_EXIST);
                    }
                    $replies[$key] = array_merge($replies[$key], ['updated_at' => time(), 'edited_flag' => StatusConstant::YES] + Arr::only($input, ['contents', 'atWho']));
                    $model->reply = $replies;
                    break;
                case 'delReply':
                    $replyId = $input['reply_id'] ?? null;
                    if (! $replyId) {
                        throw new BusinessException(ErrorCode::ISSUE_COMMENT_REPLY_ID_NOT_EXIST);
                    }

                    $replies = $model->reply;
                    $key = collect($replies)->search(static function ($item) use ($replyId) {
                        return $item['id'] == $replyId;
                    });
                    if (! $key) {
                        throw new BusinessException(ErrorCode::ISSUE_COMMENT_REPLY_NOT_EXIST);
                    }
                    $reply = $replies[$key];
                    // 当自用户没有删除评论权限 且 此评论是自己发布的单没有删除自己评论的权限时，报错
                    if (! $this->acl->isAllowed($user->id, Permission::DELETE_COMMNETS, $project) && ! ($reply['creator']['id'] == $user->id && $this->acl->isAllowed($user->id, Permission::DELETE_SELF_COMMNETS, $project))) {
                        throw new BusinessException(ErrorCode::PERMISSION_DENIED);
                    }

                    unset($replies[$key]);
                    $model->reply = $replies;
                    break;
                default:
                    throw new BusinessException(ErrorCode::ISSUE_OPERATION_INVALID);
            }

            $model->save();
        } else {
            if (! $this->acl->isAllowed($user->id, Permission::EDIT_COMMNETS) && ! ($model->creator['id'] == $user->id && $this->acl->isAllowed($user->id, Permission::DELETE_SELF_COMMNETS, $project))) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }

            $model->edited_flag = StatusConstant::YES;
            isset($input['contents']) && $model->contents = $input['contents'];
            isset($input['atWho']) && $model->at_who = $input['atWho'];
            $model->save();
        }

        return $this->formatter->base($model);
    }
}
