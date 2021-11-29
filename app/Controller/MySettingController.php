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
use App\Service\UserAuth;
use App\Service\UserSettingService;
use Hyperf\Di\Annotation\Inject;

class MySettingController extends Controller
{
    #[Inject]
    protected UserSettingService $service;

    public function show()
    {
        $userId = UserAuth::instance()->build()->getUserId();

        return $this->response->success(
            $this->service->show($userId)
        );
    }

    public function setAvatar()
    {
        $user = UserAuth::instance()->build()->getUser();
        $data = $this->request->input('data');

        if (empty($data)) {
            throw new BusinessException(ErrorCode::AVATAR_CANNOT_EMPTY);
        }

        $result = $this->service->setAvatar($data, $user);

        return $this->response->success($result);
    }

    public function setNotifications()
    {
        $user = UserAuth::instance()->build()->getUser();

        $model = $this->service->setNotifications($this->request->all(), $user);

        return $this->response->success([
            'notifications' => $model->notifications,
        ]);
    }
}
