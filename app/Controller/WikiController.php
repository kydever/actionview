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

    public function getDirTree()
    {
        // d 父级  1文件夹   否则就是文件（文档）
        // id 字符串
        // currentnode  传root 或者没有这个字段
        // parent 为"0"时候 代表为根目录  否则存储是  id
        // pt 默认为 ["0"],["0","12313"]

        $dt = ['id' => '0', 'name' => '根目录', 'd' => 1];

        $curnode = $this->request->input('currentnode');
        if (! $curnode) {
            $curnode = '0';
        }

        $pt = ['0'];
        if ($curnode !== '0') {
            $node = DB::collection('wiki_' . $project_key)
                ->where('_id', $curnode)
                ->first();

            if ($node) {
                $pt = $node['pt'];
                if (isset($node['d']) && $node['d'] == 1) {
                    array_push($pt, $curnode);
                }
            }
        }

        foreach ($pt as $val) {
            $sub_dirs = DB::collection('wiki_' . $project_key)
                ->where('parent', $val)
                ->where('del_flag', '<>', 1)
                ->get();

            $this->addChildren2Tree($dt, $val, $sub_dirs);
        }

        return $this->response->success($dt);
    }
}
