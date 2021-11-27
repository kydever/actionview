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

use App\Constants\StatusConstant;
use App\Request\PaginationRequest;
use App\Request\ProjectMiniRequest;
use App\Request\ProjectStoreRequest;
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
        $isExisted = $this->dao->exists($key);

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

    public function stats()
    {
        return $this->response->success();
    }

    public function index(PaginationRequest $request)
    {
        [$result, $options] = $this->service->index($request->all(), $request->offset(), $request->limit());

        return $this->response->success($result, ['options' => $options]);
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