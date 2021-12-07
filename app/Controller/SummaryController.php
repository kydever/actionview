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
use App\Service\ProjectSummaryService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class SummaryController extends Controller
{
    #[Inject]
    protected ProjectSummaryService $service;

    public function index()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->index($project, $user);

        return $this->response->success($result);
    }
}
