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

use App\Service\ProjectAuth;
use App\Service\RoleService;
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

    public function setPermissions(int $id)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->setPermissions($project, $id);

        return $this->response->success($result);
    }
}
