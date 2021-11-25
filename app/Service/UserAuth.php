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
use Hyperf\Redis\Redis;
use Hyperf\Utils\Traits\StaticInstance;

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

        return $this;
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
        return $this->user;
    }

    private function getKey(): string
    {
        return 'auth:' . $this->token;
    }
}
