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

use App\Request\GetIssuesReportRequest;
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

    public function getIssues(GetIssuesReportRequest $request)
    {
        $x = $request->input('stat_x');
        $y = $request->input('stat_y');
        $input = $request->all();

        $user = get_user();
        $project = get_project();

        $result = $this->service->getIssues($x, $y, $user, $project, $input);

        return $this->response->success($result);
    }

    public function getTrends()
    {
        [ $data, $options ] = $this->service->getTrends($this->request->all());

        return $this->response->success($data, $options);
    }

    public function getTimetracks()
    {
        $result = $this->service->getTimetracks();

        return $this->response->success($result);
    }

    public function getTimetracksDetail(int $id)
    {
        $result = $this->service->getTimetracksDetail($id);

        return $this->response->success($result);
    }
}
