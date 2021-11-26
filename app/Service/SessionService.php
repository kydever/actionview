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
        $user = di()->get(UserService::class)->login($email, $password);

        $project = di()->get(ProjectService::class)->getLatestAccessProject($user->id);

        $result = $this->formatter->base($user);
        $result['latest_access_project'] = $project?->key;
        return $result;
    }
}
