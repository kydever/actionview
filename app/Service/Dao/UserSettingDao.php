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
namespace App\Service\Dao;

use App\Model\UserSetting;
use Han\Utils\Service;

class UserSettingDao extends Service
{
    public function first(int $userId): ?UserSetting
    {
        return UserSetting::findFromCache($userId);
    }
}
