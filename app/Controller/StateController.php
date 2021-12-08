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
use App\Service\StateService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class StateController extends Controller
{
    #[Inject]
    protected StateService $service;

    public function index()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->index($project, $user);

        return $this->response->success($result);
    }
}
