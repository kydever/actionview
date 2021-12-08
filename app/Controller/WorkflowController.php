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

use App\Service\WorkflowService;
use Hyperf\Di\Annotation\Inject;

class WorkflowController extends Controller
{
    #[Inject]
    protected WorkflowService $service;

    public function preview(int $id)
    {
        return $this->response->success(
            $this->service->preview($id)
        );
    }
}
