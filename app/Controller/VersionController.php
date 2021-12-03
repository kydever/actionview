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

use App\Request\PaginationRequest;
use App\Request\VersionReleaseRequest;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use App\Service\VersionService;
use Hyperf\Di\Annotation\Inject;

class VersionController extends Controller
{
    #[Inject]
    protected VersionService $service;

    public function store()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->store($this->request->all(), $user, $project);

        return $this->response->success($result);
    }

    public function update(int $id)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->update($id, $this->request->all(), $user, $project);

        return $this->response->success($result);
    }

    public function index(PaginationRequest $request)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        [$result, $extra] = $this->service->index($project, $request->offset(), $request->limit());
        return $this->response->success(
            $result,
            ['option' => $extra],
        );
    }

    public function release(VersionReleaseRequest $request, int $id)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->release($id, $request->all(), $user, $project);

        return $this->response->success($result);
    }
}
