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
namespace App\Service\Context;

use App\Service\Dao\UserDao;
use Han\Utils\ContextInstance;

class UserContext extends ContextInstance
{
    protected function initModels(array $ids): array
    {
        return di()->get(UserDao::class)->findMany($ids)->getDictionary();
    }
}
