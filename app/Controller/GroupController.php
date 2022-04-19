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

use App\Request\GroupSearchRequest;
use App\Request\GroupStoreRequest;
use App\Request\GroupUpdateRequest;
use App\Request\PaginationRequest;
use App\Service\GroupService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class GroupController extends Controller
{
    #[Inject]
    protected GroupService $service;

    public function index(GroupSearchRequest $request, PaginationRequest $page)
    {
        [$count, $result] = $this->service->index($request->all(), $page->offset(), $page->limit());

        return $this->response->success($result, [
            'options' => [
                'total' => $count,
                'sizePerPage' => $page->limit(),
                'directories' => [],
            ],
        ]);
    }

    public function store(GroupStoreRequest $request)
    {
        $user = UserAuth::instance()->build()->getUser();

        return $this->response->success(
            $this->service->store(0, $request->all(), $user)
        );
    }

    public function update(GroupUpdateRequest $request, int $id)
    {
        $user = UserAuth::instance()->build()->getUser();

        return $this->response->success(
            $this->service->store($id, $request->all(), $user)
        );
    }

    public function destroy(int $id)
    {
        $user = UserAuth::instance()->build()->getUser();

        $id = $this->service->destroy($id, $user);
        return $this->response->success([
            'id' => $id,
        ]);
    }

    public function mygroup(PaginationRequest $request)
    {
        $userId = UserAuth::instance()->build()->getUserId();
        $input = $request->all();

        [$count, $result] = $this->service->mygroup($input, $userId, $request->offset(), $request->limit());

        return $this->response->success($result, [
            'options' => [
                'total' => $count,
                'sizePerPage' => $request->limit(),
            ],
        ]);
    }

    public function search()
    {
        $s = $this->request->input('s');
        $groups = $this->service->searchGroup($s);

        return $this->response->success($groups);
    }
}
