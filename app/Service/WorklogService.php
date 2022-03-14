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
use App\Model\Project;
use App\Model\User;
use App\Model\Worklog;
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

    public function save(Project $project, int $issueId, User $user, array $attributes, int $worklogId = 0): array
    {
        if (! $this->acl->isAllowed($user->id, 'add_worklog', $project)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $spend = $this->ttHandle($attributes['spend']);
        $spendM = $this->ttHandleInM($attributes['spend']);
        $adjustType = $attributes['adjust_type'];

        $leaveEstimate = $attributes['leave_estimate'] ?? '';
        if ($adjustType == 3) {
            if (empty($leaveEstimate)) {
                throw new BusinessException(ErrorCode::WORKLOG_LEAVE_ESTIMATE_TIME_CANNOT_EMPTY);
            }
            if (! $this->ttCheck($leaveEstimate)) {
                throw new BusinessException(ErrorCode::WORKLOG_LEAVE_ESTIMATE_TIME_INVALID);
            }
            $leaveEstimate = $this->ttHandle($attributes['leave_estimate']);
            $leaveEstimateM = $this->ttHandleInM($attributes['leave_estimate']);
        }
        $cut = $attributes['cut'] ?? '';
        if ($adjustType == 4) {
            if (empty($cut)) {
                throw new BusinessException(ErrorCode::WORKLOG_CUT_CANNOT_EMPTY);
            }
            if (! $this->ttCheck($cut)) {
                throw new BusinessException(ErrorCode::WORKLOG_CUT_INVALUD);
            }
            $cut = $this->ttHandle($attributes['cut']);
            $cutM = $this->ttHandleInM($attributes['cut']);
        }

        di(IssueDao::class)->first($issueId, true);

        $model = $this->dao->findById($worklogId);
        if (empty($model)) {
            $model = new Worklog();
            $model->recorder = json_encode([
                'id' => $user->id,
                'name' => $user->first_name,
                'email' => $user->email,
            ]);
            $model->recorded_at = time();
            $model->started_at = $attributes['started_at'];
        }
        $model->project_key = $project->key;
        $model->issue_id = $issueId;
        $model->spend = $spend;
        $model->spend_m = $spendM;
        $model->adjust_type = $adjustType;
        $model->comments = $attributes['comments'] ?? '';
        $model->leave_estimate = $leaveEstimate;
        $model->cut = $cut;
        $model->edited_flag = isset($attributes['edited_flag']) ?? 1;
        $model->save();

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
