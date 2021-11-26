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
use App\Service\Formatter\UserFormatter;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class UserController extends Controller
{
    #[Inject]
    protected UserService $service;

    #[Inject]
    protected UserFormatter $formatter;

    public function login(SessionCreateRequest $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = $this->service->login($email, $password);

        return $this->response->success(
            $this->formatter->base($user)
        );
    }

    public function register()
    {
    }
}
