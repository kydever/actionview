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

use App\Service\ReportService;
use App\Service\TimeTrackTrait;
use Hyperf\Di\Annotation\Inject;

class ReportController extends Controller
{
    use TimeTrackTrait;

    #[Inject]
    protected ReportService $service;

    public function index()
    {
        return $this->response->success(
            $this->service->index()
        );
    }
}
