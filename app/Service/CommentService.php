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
use App\Exception\BusinessException;
use App\Model\Comment;
use App\Model\Project;
use App\Model\User;
use App\Service\Dao\CommentDao;
use App\Service\Formatter\CommentFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

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
}
