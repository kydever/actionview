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
use App\Exception\BusinessException;
use App\Service\AclService;
use App\Service\FileService;
use App\Service\ProjectAuth;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class FileController extends Controller
{
    #[Inject()]
    protected FileService $service;

    public function getAvatar()
    {
        $fid = $this->request->input('fid');
        if (empty($fid)) {
            throw new BusinessException(ErrorCode::AVATAR_ID_NOT_EMPTY);
        }

        $filename = $this->service->getAvatar($fid);

        return $this->response->image($filename);
    }

    public function upload()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();

        if (! di()->get(AclService::class)->isAllowed($user->id, Permission::UPLOAD_FILE, $project)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        $files = $this->request->file('attachments');
        if (! is_array($files)) {
            $files = [$files];
        }

        $result = $this->service->upload($files);

        return $this->response->success($result);
    }
}
