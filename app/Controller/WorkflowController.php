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
use App\Service\Dao\ConfigTypeDao;
use App\Service\Formatter\DefinitionFormatter;
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
    protected DefinitionFormatter $formatter;

    public function preview(int $id)
    {
        return $this->response->success(
            $this->service->preview($id)
        );
    }

    public function index()
    {
        $workflows = $this->provider->getWorkflowList(
            get_project_key(),
            ['id', 'name', 'project_key', 'description', 'latest_modified_time', 'latest_modifier', 'steps']
        );
        $configTypeDao = di()->get(ConfigTypeDao::class);
        foreach ($workflows as $workflow) {
            $workflow->is_used = $configTypeDao->existsByWorkFlowId($workflow->id);
        }

        return $this->response->success($workflows);
    }

    public function store ( WorkflowRequest $request )
    {
        $model = $this->service->save(0, get_user(), get_project_key(), $request->all());

        return $this->formatter->base($model);
    }
}
