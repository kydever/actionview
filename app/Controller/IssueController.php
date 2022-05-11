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
namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Request\IssueBatchHandleRequest;
use App\Request\IssueStoreRequest;
use App\Request\PaginationRequest;
use App\Service\Dao\IssueDao;
use App\Service\IssueService;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class IssueController extends Controller
{
    #[Inject]
    protected IssueService $service;

    public function index(PaginationRequest $request)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        [, $result, $options] = $this->service->index($request->all(), $project, $user, $request->offset(), $request->limit());

        return $this->response->success($result, [
            'options' => $options,
        ]);
    }

    public function show(int $id)
    {
        $issue = di()->get(IssueDao::class)->first($id);
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->show($issue, $user, $project);

        return $this->response->success($result);
    }

    public function getOptions()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->getAllOptions($user->id, $project);

        return $this->response->success($result);
    }

    public function store(IssueStoreRequest $request)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();
        $input = $request->all();

        $result = $this->service->store($input, $user, $project);

        return $this->response->success($result);
    }

    public function update(int $id)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();
        $input = $this->request->all();

        $result = $this->service->update($id, $input, $user, $project);

        return $this->response->success($result);
    }

    public function setAssignee(int $id)
    {
        $assigneeId = (string) $this->request->input('assignee');
        if (empty($assigneeId)) {
            throw new BusinessException(ErrorCode::ISSUE_ASSIGNEE_CANNOT_EMPTY);
        }

        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();
        $result = $this->service->setAssignee($id, $assigneeId, $user, $project);

        return $this->response->success($result);
    }

    public function resetState(int $id)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->resetState($this->request->all(), $id, $user, $project);

        return $this->response->success($result);
    }

    public function saveIssueFilter()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->saveIssueFilter($this->request->all(), $user, $project);

        return $this->response->success($result);
    }

    public function getIssueFilters()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->getIssueFilters($project, $user);

        return $this->response->success($result);
    }

    public function resetIssueFilters()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->resetIssueFilters($project, $user);

        return $this->response->success($result);
    }

    public function batchHandleFilters()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->batchHandleFilters($this->request->all(), $user, $project);

        return $this->response->success($result);
    }

    public function batchHandle(IssueBatchHandleRequest $request)
    {
        $data = $request->input('data');

        $project = get_project();
        $user = get_user();

        $result = match ($request->getInputMethod()) {
            'update' => $this->service->batchUpdate($project, $user, $data['ids'], $data['values']),
            'delete' => $this->service->batchDelete($data['ids'], $project, $user),
        };

        return $this->response->success($result);
    }

    public function doAction(int $id, int $workflowId)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->doAction($id, $workflowId, $this->request->all(), $user, $project);

        return $this->response->success($result);
    }

    public function wfactions(int $id)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->wfactions($id, $user, $project);

        return $this->response->success($result);
    }

    public function getHistory(int $id)
    {
        $sort = $this->request->input('sort') == 'asc' ? 'asc' : 'desc';
        $projectKey = get_project_key();

        $result = $this->service->getHistory($id, $sort, $projectKey);

        return $this->response->success($result);
    }

    public function watch(int $id)
    {
        $project = get_project();
        $user = get_user();
        $flag = (bool) $this->request->input('flag');
        $data = $this->service->watch($id, $flag, $project, $user);

        return $this->response->success($data);
    }

    public function destroy(int $id)
    {
        $project = get_project();
        $user = get_user();
        $result = $this->service->delete($id, $project, $user);

        return $this->response->success($result);
    }
}
