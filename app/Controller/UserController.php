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

use App\Request\PaginationRequest;
use App\Request\SessionCreateRequest;
use App\Request\UserRegisterRequest;
use App\Request\UserSearchRequest;
use App\Request\UserStoreRequest;
use App\Request\UserUpdateRequest;
use App\Service\Dao\UserDao;
use App\Service\Formatter\UserFormatter;
use App\Service\GroupService;
use App\Service\UserAuth;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class UserController extends Controller
{
    #[Inject]
    protected UserDao $dao;

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

    public function register(UserRegisterRequest $request)
    {
        $firstName = $request->input('first_name');
        $email = $request->input('email');
        $password = $request->input('password');

        $result = $this->service->register($email, $firstName, $password);

        return $this->response->success($result);
    }

    public function search()
    {
        $keyword = $this->request->input('s');

        return $this->response->success(
            $this->service->search($keyword)
        );
    }

    public function index(UserSearchRequest $request, PaginationRequest $page)
    {
        [$count, $result] = $this->service->index($request->all(), $page->offset(), $page->limit());

        return $this->response->success($result, [
            'options' => [
                'total' => $count,
                'sizePerPage' => $page->limit(),
                'groups' => di()->get(GroupService::class)->getAll(),
                'directories' => [],
            ],
        ]);
    }

    public function store(UserStoreRequest $request)
    {
        $user = UserAuth::instance()->build()->getUser();

        return $this->response->success(
            $this->service->store(0, $request->all(), $user)
        );
    }

    public function update(int $id, UserUpdateRequest $request)
    {
        $user = UserAuth::instance()->build()->getUser();

        return $this->response->success(
            $this->service->update($id, $request->all(), $user)
        );
    }
}
