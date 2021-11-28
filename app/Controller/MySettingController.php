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

    /**
     * @TODO: limx
     */
    public function setAvatar()
    {
        $userId = UserAuth::instance()->build()->getUserId();

        $result = $this->service->setAvatar($userId);

        return $this->response->success($result);
    }
}
