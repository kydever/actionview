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
namespace App\Constants;

class UserConstant
{
    public const INVALID_FLAG = 1;

    public const VALID_FLAG = 0;

    public const ACTIVE = 'active';

    public const INVALID = 'invalid';

    public static function isSuperAdmin(int $userId): bool
    {
        return $userId === 1;
    }
}
