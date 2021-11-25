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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\Dao\SysSettingDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class SessionService extends Service
{
    #[Inject]
    protected SysSettingDao $sys;

    #[Inject]
    protected UserDao $dao;

    #[Inject]
    protected UserFormatter $formatter;

    public function create(string $email, string $password)
    {
        $setting = $this->sys->first();
        if (! str_contains($email, '@')) {
            if (isset($setting->properties['login_mail_domain'])) {
                $email = $email . '@' . $setting->properties['login_mail_domain'];
            }
        }

        $user = $this->dao->firstByEmail($email);
        if (! $user?->verify($password)) {
            // 用户名密码错误
            throw new BusinessException(ErrorCode::USER_PASSWORD_INVALID);
        }

        // TODO: LDAP

        if ($user->isInvalid()) {
            throw new BusinessException(ErrorCode::USER_DISABLED);
        }

        UserAuth::instance()->init($user);

        // TODO: getLatestAccessProject

        return $this->formatter->base($user);
    }
}
