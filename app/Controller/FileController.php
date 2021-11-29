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
use App\Service\FileService;
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
}
