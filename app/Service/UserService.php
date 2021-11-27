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

use App\Acl\Eloquent\Group;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use App\Service\Dao\AclGroupDao;
use App\Service\Dao\SysSettingDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\UserFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;

class UserService extends Service
{
    #[Inject]
    protected SysSettingDao $sys;

    #[Inject]
    protected UserDao $dao;

    #[Inject]
    protected UserFormatter $formatter;

    public function login(string $email, string $password)
    {
        if (! str_contains($email, '@')) {
            $setting = $this->sys->first();
            if (isset($setting->properties['login_mail_domain'])) {
                $email = $email . '@' . $setting->properties['login_mail_domain'];
            }
        }

        $user = $this->dao->firstByEmail($email);
        if (! $user?->verify($password)) {
            // 用户名密码错误
            throw new BusinessException(ErrorCode::USER_PASSWORD_INVALID);
        }

        if ($user->isInvalid()) {
            throw new BusinessException(ErrorCode::USER_DISABLED);
        }

        UserAuth::instance()->init($user);

        return $user;
    }

    public function register(string $email, string $firstName, string $password)
    {
        if ($this->dao->firstByEmail($email)) {
            throw new BusinessException(ErrorCode::EMAIL_ALREADY_REGISTERED);
        }

        $user = new User();
        $user->email = $email;
        $user->first_name = $firstName;
        $user->password = $password;
        $user->save();

        return $this->formatter->base($user);
    }

    public function search(string $keyword): array
    {
        if (! $keyword) {
            return [];
        }

        $models = $this->dao->findByKeyword($keyword);
        $result = [];
        foreach ($models as $model) {
            if ($model->isSuperAdmin()) {
                continue;
            }

            $result[] = $this->formatter->small($model);
        }

        return $result;
    }

    /**
     * @param $input = [
     *     'group' => 1,
     * ]
     */
    public function index(array $input, int $offset = 0, int $limit = 10)
    {
        if (! empty($input['group'])) {
            $groupId = $input['group'];
            $group = di()->get(AclGroupDao::class)->first($groupId, false);

            $input['ids'] = $group?->users;
        }

        [$total, $models] = $this->dao->find($input, $offset, $limit);

        $models->load('groups');

        return [$total, $this->formatter->formatList($models)];
    }
}