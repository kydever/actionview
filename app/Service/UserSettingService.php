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
namespace App\Service;

use App\Service\Dao\UserDao;
use App\Service\Dao\UserSettingDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class UserSettingService extends Service
{
    #[Inject]
    protected UserSettingDao $dao;

    public function show(int $userId)
    {
        $user = di()->get(UserDao::class)->first($userId, true);
        $userSetting = $this->dao->first($userId);

        return [
            'notifications' => $userSetting?->notifications ?? ['mail_notify' => true],
            'favorites' => $userSetting?->favorites,
            'accounts' => di()->get(UserFormatter::class)->base($user),
        ];
    }
}
