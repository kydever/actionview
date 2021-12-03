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

class StatusConstant
{
    public const NOT_DELETED = 0;

    public const DELETED = 1;

    public const ASC = 'asc';

    public const DESC = 'desc';

    public const AVAILABLE = 1;

    public const UN_AVAILABLE = 2;

    // 公开
    public const SCOPE_PUBLIC = 1;

    // 私有
    public const SCOPE_PRIVATE = 2;

    // 成员可见
    public const SCOPE_MEMBER = 3;

    public const STATUS_UNRESOLVED = 'Unresolved';

    public const STATUS_UNRELEASED = 'unreleased';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_RELEASED = 'released';
}
