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

use App\Request\BoardRequest;
use App\Service\BoardService;
use App\Service\Formatter\BoardFormatter;
use App\Service\ProjectAuth;
use App\Service\ProviderService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class BoardController extends Controller
{
    #[Inject]
    protected BoardService $service;

    #[Inject]
    protected BoardFormatter $formatter;

    #[Inject]
    protected ProviderService $provider;

    public function index()
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $user = UserAuth::instance()->build()->getUser();

        [$result, $options] = $this->service->index($user, $project);

        return $this->response->success([
            $result,
            [
                'options' => $options,
            ],
        ]);
    }

    public function store(BoardRequest $request)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $states = $this->provider->getStateListOptions($project->key);
        $model = $this->service->create($project->key, $states, $request->all());

        return $this->response->success(
            $this->formatter->base($model)
        );
    }

    public function update(BoardRequest $request, int $id)
    {
        $model = $this->service->update($id, $this->getProjectKey(), $request->all());

        return $this->response->success(
            $this->formatter->base($model)
        );
    }
}
