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

use App\Request\RolePermissionSaveRequest;
use App\Service\ProjectAuth;
use App\Service\RoleService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class RoleController extends Controller
{
    #[Inject]
    protected RoleService $service;

    public function index()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->index($project);

        return $this->response->success($result);
    }

    public function setPermissions(RolePermissionSaveRequest $request, int $id)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->setPermissions($request->all(), $id, $project, $user);

        return $this->response->success($result);
    }
}
