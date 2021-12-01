<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\WikiCreateRequest;
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

    public function create(WikiCreateRequest $request)
    {
        $input = $request->all();
        if (isset($input['d']) && $input['d'] == 1) {
            $s = $this->service->isPermissionAllowed($input['project_key'], 'manage_project');
            if (! $this->service->isPermissionAllowed($input['project_key'], 'manage_project')) {
                $result = ['ecode' => -10002, 'emsg' => 'permission denied.'];
            }else{
                $result = $this->service->createFolder($input);
            }

        }else{
            $result = $this->service->createDoc($input);
        }
        return $this->response->success($result);
    }
}
