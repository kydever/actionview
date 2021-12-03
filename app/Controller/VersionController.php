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
use App\Service\ProjectAuth;
use App\Service\VersionService;
use Hyperf\Di\Annotation\Inject;

class VersionController extends Controller
{
    #[Inject]
    protected VersionService $service;

    public function index(PaginationRequest $request)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        [$result, $extra] = $this->service->index($project, $request->offset(), $request->limit());
        return $this->response->success(
            $result,
            ['option' => $extra],
        );
    }
}
