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
use App\Request\SaveFilterReportRequest;
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
            $this->service->index(get_project_key(), get_user_id())
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

    public function saveFilter(SaveFilterReportRequest $request, string $mode)
    {
        $user = get_user_id();
        $project = get_project_key();
        $result = $this->service->saveFilter($mode, $request->all(), $user, $project);

        return $this->response->success($result);
    }

    public function getTrends()
    {
        [$data, $options] = $this->service->getTrends($this->request->all());

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

    public function editSomeFilters(string $mode)
    {
        return $this->response->success(
            $this->service->editSomeFilters($mode, $this->request->all(), get_project_key(), get_user_id())
        );
    }
}
