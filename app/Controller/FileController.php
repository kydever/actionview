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
use App\Request\DeleteFileRequest;
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

        $filename = $this->service->getFile($fid);

        return $this->response->download($filename);
    }

    public function upload()
    {
        $user = UserAuth::instance()->build()->getUser();
        $project = ProjectAuth::instance()->build()->getCurrent();
        $issueId = (int) $this->request->input('issue_id');

        if (! di()->get(AclService::class)->isAllowed($user->id, Permission::UPLOAD_FILE, $project)) {
            throw new BusinessException(ErrorCode::PERMISSION_DENIED);
        }

        if (empty($issueId)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, 'issue_id is required');
        }

        $files = $this->request->getUploadedFiles();

        $result = $this->service->upload($files, $user, $issueId);

        return $this->response->success($result);
    }

    public function thumbnail(int $id)
    {
        $path = $this->service->thumbnail($id);

        return $this->response->download($path);
    }

    public function download(int $id, ?string $name = null)
    {
        $path = $this->service->download($id);

        return $this->response->download($path);
    }

    public function delete(DeleteFileRequest $request, int $id)
    {
        $project = get_project();
        $user = get_user();
        $result = $this->service->delete($id, $request->all(), $project, $user);

        return $this->response->success($result);
    }
}
