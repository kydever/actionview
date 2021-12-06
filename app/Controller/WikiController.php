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

use App\Constants\ErrorCode;
use App\Constants\Permission;
use App\Constants\ProjectConstant;
use App\Exception\BusinessException;
use App\Kernel\Http\Response;
use App\Request\WikiCreateRequest;
use App\Request\WikiGetDirTreeRequest;
use App\Request\WikiIndexRequest;
use App\Request\WikiSearchPathRequest;
use App\Request\WikiShowRequest;
use App\Request\WikiUpdateRequest;
use App\Service\AclService;
use App\Service\Dao\WikiDao;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use App\Service\WikiService;
use Hyperf\Di\Annotation\Inject;

class WikiController extends Controller
{
    #[Inject]
    protected WikiService $service;

    protected Response $response;

    public function create(WikiCreateRequest $request)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $input = $request->all();
        if (($input['d'] ?? null) == ProjectConstant::WIKI_FOLDER) {
            if (! di()->get(AclService::class)->isAllowed($user->id, Permission::MANAGE_PROJECT, $project)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }

            $result = $this->service->createFolder($input, $user, $project);
            return $this->response->success($result);
        }

        [$data, $path] = $this->service->createDoc($input, $user, $project);
        return $this->response->success($data, ['option' => ['path' => $path]]);
    }

    // 有问题  currentnode 会传 root  默认赋值为 0
//    public function getDirTree(WikiGetDirTreeRequest $request)
//    {
//        $curnode = $request->input('currentnode') ?? 0;
//        $dt = ['id' => '0', 'name' => '根目录', 'd' => 1];
//        $project = ProjectAuth::instance()->build()->getCurrent();
//        $result = $this->service->getDirTree($curnode, $dt, $project);
//
//        return $this->response->success($result);
//    }

    public function index(WikiIndexRequest $request, int $directory)
    {
        $project = ProjectAuth::instance()->build()->getCurrent();
        $input = $request->all();
        [$result, $path, $home] = $this->service->index($input, $directory, $project);

        return $this->response->success($result, ['options' => ['path' => $path, 'home' => $home]]);
    }

    public function destroy(int $id)
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        $id = $this->service->destroy($id, $project, $user);

        return $this->response->success(['id' => $id]);
    }

    public function update(WikiUpdateRequest $request, int $id)
    {
        $input = $request->all();
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();
        [$result, $path] = $this->service->update($input, $id, $project, $user);
        return $this->response->success($result, ['path' => $path]);
    }

    public function searchPath(WikiSearchPathRequest $request)
    {
        $input = $request->all();
        $project = ProjectAuth::instance()->build()->getCurrent();
        if (! isset($input['s'])) {
            return $this->response->success([]);
        }
        if ($input['s'] === '/') {
            return $this->response->success(['id' => 0, 'name' => '/']);
        }
        $result = $this->service->searchPath($input, $project);

        return $this->response->success($result);
    }

    public function show(WikiShowRequest $request, $id)
    {
        $input = $request->all();
        $model = di()->get(WikiDao::class)->first($id, true);
        $user = UserAuth::instance()->build()->getUser();
        [$data, $path] = $this->service->show($input, $model, $user);

        return $this->response->success($data, ['path' => $path]);
    }
}
