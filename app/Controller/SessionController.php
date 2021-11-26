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
use App\Service\SessionService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;

class SessionController extends Controller
{
    #[Inject()]
    protected SessionService $service;

    public function create(SessionCreateRequest $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $result = $this->service->create($email, $password);

        $response = $this->response->response();
        if ($response instanceof Response) {
            $response = $response->withCookie(new Cookie(UserAuth::X_TOKEN, UserAuth::instance()->getToken()));
            Context::set(ResponseInterface::class, $response);
        }

        return $this->response->success([
            'user' => $result,
        ]);
    }

    public function getSession()
    {
        $user = Sentinel::getUser();
        if ($user) {
            $latest_access_project = $this->getLatestAccessProject($user->id);
            if ($latest_access_project) {
                $user->latest_access_project = $latest_access_project->key;
            }
            return Response()->json(['ecode' => 0, 'data' => ['user' => $user]]);
        }

        return Response()->json(['ecode' => -10001, 'data' => ['user' => []]]);
    }
}
