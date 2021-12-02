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
use App\Request\PaginationRequest;
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
