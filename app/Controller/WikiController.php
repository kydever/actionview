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
use App\Exception\BusinessException;
use App\Kernel\Http\Response;
use App\Request\WikiCreateRequest;
use App\Service\UserAuth;
use App\Service\WikiService;
use Hyperf\Di\Annotation\Inject;

class WikiController extends Controller
{
    #[Inject]
    protected WikiService $service;

    protected Response $response;

    public function create(WikiCreateRequest $request, $project_key)
    {
        $user = UserAuth::instance()->build()->getUser();
        $input = $request->all();
        $input['project_key'] = $project_key;

        if (isset($input['d']) && $input['d'] == 1) {
            if (! $this->service->isPermissionAllowed($input['project_key'], 'manage_project', $user)) {
                throw new BusinessException(ErrorCode::PERMISSION_DENIED);
            }
            $result = $this->service->createFolder($input, $user);
            return $this->response->success($result);
        }

        [$data,$option] = $this->service->createDoc($input, $user);
        return $this->response->success($data, ['option' => ['path' => $option]]);
    }
}
