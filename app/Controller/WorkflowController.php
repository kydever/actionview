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

use App\Request\WorkflowRequest;
use App\Service\Formatter\OswfDefinitionFormatter;
use App\Service\ProviderService;
use App\Service\WorkflowService;
use Hyperf\Di\Annotation\Inject;

class WorkflowController extends Controller
{
    #[Inject]
    protected WorkflowService $service;

    #[Inject]
    protected ProviderService $provider;

    #[Inject]
    protected OswfDefinitionFormatter $formatter;

    public function preview(int $id)
    {
        return $this->response->success(
            $this->service->preview($id)
        );
    }

    public function index()
    {
        $workflows = $this->provider->getWorkflowList(get_project_key());

        return $this->response->success($workflows);
    }

    public function store(WorkflowRequest $request)
    {
        $model = $this->service->save(0, get_user(), get_project_key(), $request->all());

        return $this->formatter->base($model);
    }

    public function update(WorkflowRequest $request, int $id)
    {
        $model = $this->service->save(
            $id,
            get_user(),
            get_project_key(),
            $request->all()
        );

        return $this->formatter->base($model);
    }
}
