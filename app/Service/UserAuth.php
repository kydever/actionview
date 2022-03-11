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
use App\Model\User;
use App\Service\Dao\UserDao;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Traits\StaticInstance;
use Psr\Http\Message\ResponseInterface;

class UserAuth
{
    use StaticInstance;

    public const X_TOKEN = 'x-token';

    private int $userId = 0;

    private string $token = '';

    private ?User $user = null;

    public function load(string $token): static
    {
        $this->token = $token;

        $id = (int) di()->get(Redis::class)->get($this->getKey());

        if ($id > 0) {
            $this->userId = $id;
            di()->get(Redis::class)->expire($this->getKey(), 86400 * 14);
        }

        return $this;
    }

    public function init(User $user): static
    {
        $this->userId = $user->id;
        $this->token = md5(uniqid() . $user->id);
        $this->user = $user;

        di()->get(Redis::class)->set($this->getKey(), $this->userId, 86400 * 14);

        $this->appendTokenToCookies();

        return $this;
    }

    public function destroy(): void
    {
        if ($this->token) {
            di()->get(Redis::class)->del($this->getKey());
        }

        $this->token = '';
        $this->appendTokenToCookies();
    }

    public function build(): static
    {
        if ($this->userId <= 0) {
            throw new BusinessException(ErrorCode::TOKEN_INVALID);
        }

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): ?User
    {
        if ($this->userId === 0) {
            return null;
        }

        if ($this->user) {
            return $this->user;
        }

        return $this->user = di()->get(UserDao::class)->first($this->userId, true);
    }

    protected function appendTokenToCookies(): void
    {
        $response = Context::get(ResponseInterface::class);
        if ($response instanceof Response) {
            $response = $response->withCookie(new Cookie(self::X_TOKEN, $this->token));
            Context::set(ResponseInterface::class, $response);
        }
    }

    private function getKey(): string
    {
        return 'auth:' . $this->token;
    }
}
