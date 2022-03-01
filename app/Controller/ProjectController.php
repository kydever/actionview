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

use App\Constants\ProjectConstant;
use App\Constants\StatusConstant;
use App\Request\PaginationRequest;
use App\Request\ProjectMiniRequest;
use App\Request\ProjectStoreRequest;
use App\Request\ProjectUpdateRequest;
use App\Service\Dao\ProjectDao;
use App\Service\ProjectService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class ProjectController extends Controller
{
    #[Inject]
    protected ProjectService $service;

    #[Inject]
    protected ProjectDao $dao;

    public function mine(ProjectMiniRequest $request)
    {
        $userId = UserAuth::instance()->build()->getUserId();

        [$result, $options] = $this->service->mine($userId, $request->all());

        return $this->response->success($result, [
            'options' => $options,
        ]);
    }

    public function recent()
    {
        $userId = UserAuth::instance()->build()->getUserId();

        return $this->response->success(
            $this->service->recent($userId)
        );
    }

    public function checkKey(string $key)
    {
        $isExisted = $this->dao->exists(ProjectConstant::formatProjectKey($key));

        return $this->response->success([
            'flag' => $isExisted ? StatusConstant::UN_AVAILABLE : StatusConstant::AVAILABLE,
        ]);
    }

    public function store(ProjectStoreRequest $request)
    {
        $input = $request->all();
        $userId = UserAuth::instance()->build()->getUserId();

        return $this->response->success(
            $this->service->store($userId, $input)
        );
    }

    public function update(ProjectUpdateRequest $request, int $id)
    {
        $input = $request->all();
        $user = UserAuth::instance()->build()->getUser();

        return $this->response->success(
            $this->service->update($id, $input, $user)
        );
    }

    public function createIndex(int $id)
    {
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->createIndex($id, $user);

        return $this->response->success($result);
    }

    public function stats()
    {
        $keys = $this->request->input('keys');
        $user = UserAuth::instance()->build()->getUser();

        $result = $this->service->stats(explode(',', $keys), $user);

        return $this->response->success($result);
    }

    public function index(PaginationRequest $request)
    {
        [$count, $result] = $this->service->index($request->all(), $request->offset(), $request->limit());
        return $this->response->success($result, [
            'options' => [
                'total' => $count,
                'sizePerPage' => $request->limit(),
            ],
        ]);
    }

    public function show(string $key)
    {
        $userId = UserAuth::instance()->build()->getUserId();

        $key = ProjectConstant::formatProjectKey($key);

        [$result, $permissions] = $this->service->show($key, $userId);

        return $this->response->success($result, [
            'options' => [
                'permissions' => $permissions,
            ],
        ]);
    }

    public function getOptions()
    {
    }

    public function updMultiStatus()
    {
    }

    public function createMultiIndex()
    {
    }

    public function destroy()
    {
    }
}
