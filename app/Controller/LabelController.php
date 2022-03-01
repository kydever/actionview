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
use App\Service\UserAuth;
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
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->findByProject($project);

        return $this->response->success($result);
    }

    public function store(LabelRequest $request, string $project_key)
    {
        $name = $request->input('name');
        $bgColor = $request->input('bgColor');
        $created = $this->service->createOrUpdate($name, $project_key, $bgColor);

        return $this->response->success($created);
    }

    public function update(LabelRequest $request, string $project_key, int $id)
    {
        $name = $request->input('name');
        $bgColor = $request->input('bgColor');
        $updated = $this->service->createOrUpdate($name, $project_key, $bgColor, $id);

        return $this->response->success($updated);
    }

    public function delete(int $id)
    {
        $deleted = $this->service->delete($id);

        return $this->response->success($deleted);
    }
}
