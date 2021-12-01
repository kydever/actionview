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

use App\Request\IssueStoreRequest;
use App\Service\IssueService;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class IssueController extends Controller
{
    #[Inject]
    protected IssueService $service;

    public function index()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        return $this->response->success([]);
        [$count, $result] = $this->service->index($project, $user);
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
}
