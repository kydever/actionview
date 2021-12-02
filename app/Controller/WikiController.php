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

use App\Request\WikiCreateRequest;
use App\Service\UserAuth;
use App\Service\WikiService;
use Hyperf\Di\Annotation\Inject;

class WikiController extends Controller
{
    //if (isset($d) && $d == 1)
    //{
    //if (!$this->isPermissionAllowed($project_key, 'manage_project'))
    //{
    //return Response()->json(['ecode' => -10002, 'emsg' => 'permission denied.']);
    //}
    //return $this->createFolder($request, $project_key);
    //}
    //else
    //{
//    return $this->createDoc($request, $project_key);
    //}

    #[Inject]
    protected WikiService $service;

    public function create(WikiCreateRequest $request, $project_key)
    {
        $user = UserAuth::instance()->build()->getUser();
        $input = $request->all();
        $input['project_key'] = $project_key;

        if (isset($input['d']) && $input['d'] == 1) {
            // d
            $s = $this->service->isPermissionAllowed($input['project_key'], 'manage_project');
            if (! $this->service->isPermissionAllowed($input['project_key'], 'manage_project')) {
                return ['ecode' => -10002, 'emsg' => 'permission denied.'];
            }
            return $this->service->createFolder($input);
        }
        return $this->service->createDoc($input, $user);
    }

}
