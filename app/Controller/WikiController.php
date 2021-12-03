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
use App\Service\AclService;
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

        [$data, $option] = $this->service->createDoc($input, $user, $project);
        return $this->response->success($data, ['option' => ['path' => $option]]);
    }
}
