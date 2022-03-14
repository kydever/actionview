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
use App\Exception\BusinessException;
use App\Service\Dao\IssueDao;
use App\Service\Dao\WorklogDao;
use App\Service\Formatter\WorklogFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class WorklogService extends Service
{
    use TimeTrackTrait;

    #[Inject]
    protected WorklogDao $dao;

    #[Inject]
    protected WorklogFormatter $formatter;

    #[Inject]
    protected AclService $acl;

    public function index(int $issueId, string $sortable = 'desc'): array
    {
        $models = $this->dao->findByIssueId(get_project_key(), $issueId, $sortable);

        return $this->formatter->formatList($models);
    }

    public function create(int $issueId, array $attributes): array
    {
        $user = get_user();
        $project = get_project();
        if (! $this->acl->isAllowed($user->id, 'add_worklog', $project)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $spend = $attributes['spend'];
        if (! $this->ttCheck($spend)) {
            throw new BusinessException(ErrorCode::WORKLOG_SPEND_TIME_INVALID);
        }
        $attributes['spend'] = $this->ttHandle($spend);
        $attributes['spend_m'] = $this->ttHandleInM($spend);

        $adjustType = $attributes['adjust_type'];
        if ($adjustType == 3) {
            $leaveEstimate = $attributes['leave_estimate'] ?? null;
            if (is_null($leaveEstimate)) {
                throw new BusinessException(ErrorCode::WORKLOG_LEAVE_ESTIMATE_TIME_CANNOT_EMPTY);
            }
            if (! $this->ttCheck($leaveEstimate)) {
                throw new BusinessException(ErrorCode::WORKLOG_LEAVE_ESTIMATE_TIME_INVALID);
            }
            $attributes['leave_estimate'] = $this->ttHandle($leaveEstimate);
            $attributes['leave_estimate_m'] = $this->ttHandleInM($attributes['leave_estimate']);
        }
        if ($adjustType == 4) {
            $cut = $attributes['cut'] ?? null;
            if (is_null($cut)) {
                throw new BusinessException(ErrorCode::WORKLOG_CUT_CANNOT_EMPTY);
            }
            if (! $this->ttCheck($cut)) {
                throw new BusinessException(ErrorCode::WORKLOG_CUT_INVALUD);
            }
            $attributes['cut'] = $this->ttHandle($cut);
            $attributes['cut_m'] = $this->ttHandleInM($attributes['cut']);
        }
        if (! di()->get(IssueDao::class)->isIssueExisted($project->key)) {
            throw new BusinessException(ErrorCode::ISSUE_NOT_FOUND);
        }
        $attributes['recorder'] = [
            'id' => $user->id,
            'name' => $user->first_name,
            'meail' => $user->email,
        ];
        $model = $this->dao->create($project->key, $issueId, $attributes);

        return $this->formatter->base($model);
    }

    public function destroy(int $id): int
    {
        $user = get_user();
        $project = get_project();
        $model = $this->dao->findById($id, true);
        if (! $this->acl->isAllowed($user->id, 'delete_worklog', $project) && ! ($model->recorder['id'] == $user->id && $this->acl->isAllowed($user->id, 'delete_self_worklog', $project))) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }
        $model->delete();

        return $model->id;
    }
}
