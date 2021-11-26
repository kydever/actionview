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

use App\Request\SessionCreateRequest;
use App\Service\Dao\UserDao;
use App\Service\Formatter\UserFormatter;
use App\Service\ProjectService;
use App\Service\SessionService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class SessionController extends Controller
{
    #[Inject()]
    protected SessionService $service;

    public function create(SessionCreateRequest $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $result = $this->service->create($email, $password);

        return $this->response->success([
            'user' => $result,
        ]);
    }

    public function getSession()
    {
        $userId = UserAuth::instance()->build()->getUserId();

        $user = di()->get(UserDao::class)->first($userId, true);
        $project = di()->get(ProjectService::class)->getLatestAccessProject($userId);

        $result = di()->get(UserFormatter::class)->base($user);
        $result['latest_access_project'] = $project?->key;
        return $this->response->success([
            'user' => $result,
        ]);
    }

    public function destroy()
    {
        UserAuth::instance()->destroy();

        return $this->response->success();
    }
}
