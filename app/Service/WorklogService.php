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
        if (! $this->acl->isAllowed($user->id, Permission::ADD_WORKLOG, $project)) {
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
            'email' => $user->email,
        ];
        $model = $this->dao->create($project->key, $issueId, $attributes);
        $attributes += ['eventKey' => 'add_worklog'];
        $logs = [];
        $logs['data'] = $attributes;
        $logs['eventKey'] = 'add_worklog';
        $logs['issue'] = di(IssueDao::class)->first($issueId, true);
        $logs['user'] = [
            'id' => $user->id,
            'name' => $user->first_name,
            'email' => $user->email,
            'avatar' => $user->avatar,
        ];
        $logs['projectKey'] = $project->key;
        di(ActivityService::class)->create(array_merge($logs, compact('issueId')));

        return $this->formatter->base($model);
    }

    public function update(int $issueId, int $id, array $attributes): array
    {
        $user = get_user();
        $project = get_project();
        $model = $this->dao->findById($id, true);
        if (($project->key != $model->project_key) || $issueId != $model->issue_id) {
            throw new BusinessException(ErrorCode::WORKLOG_NOT_FOUND);
        }
        if (! $this->acl->isAllowed($user->id, Permission::EDIT_WORKLOG, $project) && ! ($model->recorder['id'] == $user->id && $this->acl->isAllowed($user->id, Permission::EDIT_SELF_WORKLOG))) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }
        $spend = $attributes['spend'] ?? $model->spend;
        if (! $this->ttCheck($spend)) {
            throw new BusinessException(ErrorCode::WORKLOG_SPEND_TIME_INVALID);
        }
        $spend = $this->ttHandle($spend);
        $spendM = $this->ttHandleInM($spend);

        $adjustType = $attributes['adjust_type'] ?? $model->adjust_type;
        if ($adjustType == 3) {
            $leaveEstimate = $attributes['leave_estimate'] ?? null;
            if (is_null($leaveEstimate)) {
                throw new BusinessException(ErrorCode::WORKLOG_LEAVE_ESTIMATE_TIME_CANNOT_EMPTY);
            }
            if (! $this->ttCheck($leaveEstimate)) {
                throw new BusinessException(ErrorCode::WORKLOG_LEAVE_ESTIMATE_TIME_INVALID);
            }
            $leaveEstimate = $this->ttHandle($leaveEstimate);
            $leaveEstimateM = $this->ttHandleInM($leaveEstimate);
            $attributes['leave_estimate'] = $leaveEstimate;
        }
        if ($adjustType == 4) {
            $cut = $attributes['cut'] ?? null;
            if (is_null($cut)) {
                throw new BusinessException(ErrorCode::WORKLOG_CUT_CANNOT_EMPTY);
            }
            if (! $this->ttCheck($cut)) {
                throw new BusinessException(ErrorCode::WORKLOG_CUT_INVALUD);
            }
            $cut = $this->ttHandle($cut);
            $cutM = $this->ttHandleInM($cut);
            $attributes['cut'] = $cut;
        }
        $model->spend = $spend;
        $model->spend_m = $spendM;
        $model->started_at = $attributes['started_at'] ?? $model->started_at;
        $model->comments = $attributes['comments'] ?? '';
        $model->leave_estimate = $attributes['leave_estimate'] ?? '';
        $model->cut = $attributes['cut'] ?? '';
        $model->edited_flag = 1;
        $model->save();

        return $this->formatter->base($model);
    }

    public function destroy(int $issueId, int $id): array
    {
        $user = get_user();
        $project = get_project();
        $model = $this->dao->findById($id, true);
        if (($project->key != $model->project_key) || ($issueId != $model->issue_id)) {
            throw new BusinessException(ErrorCode::WORKLOG_NOT_FOUND);
        }
        if (! $this->acl->isAllowed($user->id, Permission::DELETE_WORKLOG, $project) && ! ($model->recorder['id'] == $user->id && $this->acl->isAllowed($user->id, Permission::DELETE_SELF_WORKLOG, $project))) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }
        $model->delete();

        return ['id' => $model->id];
    }
}
