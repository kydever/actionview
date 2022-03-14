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

use App\Request\StoreWorklogRequest;
use App\Request\UpdateWorklogRequest;
use App\Service\WorklogService;
use Hyperf\Di\Annotation\Inject;

class WorklogController extends Controller
{
    #[Inject]
    protected WorklogService $service;

    public function index(int $id)
    {
        $sort = ($this->request->input('sort') === 'desc') ? 'desc' : 'asc';
        $result = $this->service->index($id, $sort);

        return $this->response->success($result);
    }

    public function store(StoreWorklogRequest $request, int $id)
    {
        $result = $this->service->create($id, $request->all());

        return $this->response->success($result);
    }

    public function update(UpdateWorklogRequest $request, int $id, int $worklogId)
    {
        $result = $this->service->update($id, $worklogId, $request->all());

        return $this->response->success($result);
    }

    public function destroy(int $id, int $worklogId)
    {
        $result = $this->service->destroy($id, $worklogId);

        return $this->response->success([
            'id' => $result,
        ]);
    }
}
