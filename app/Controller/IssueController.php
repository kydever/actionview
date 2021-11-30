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

        $result = $this->service->getOptions($project);

        return $this->response->success($result);
    }
}