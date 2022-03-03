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

use App\Request\LabelRequest;
use App\Service\Formatter\LabelFormatter;
use App\Service\LabelService;
use App\Service\ProjectAuth;
use Hyperf\Di\Annotation\Inject;

class LabelController extends Controller
{
    #[Inject]
    protected LabelService $service;

    #[Inject]
    protected LabelFormatter $formatter;

    public function index()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();

        $result = $this->service->findByProject($project);

        return $this->response->success($result);
    }

    public function store(LabelRequest $request)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $name = $request->input('name');
        $bgColor = $request->input('bgColor');

        $model = $this->service->save(0, $project, $name, $bgColor);

        return $this->response->success($model);
    }

    public function update(LabelRequest $request, int $id)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $name = $request->input('name');
        $bgColor = $request->input('bgColor');
        $model = $this->service->save($id, $project, $name, $bgColor);

        return $this->response->success($model);
    }

    public function delete(int $id)
    {
        $deleted = $this->service->delete($id);

        return $this->response->success($deleted);
    }
}
