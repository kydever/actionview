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

use App\Service\Dao\AclRoleactorDao;
use Han\Utils\ContextInstance;

class RoleactorContext extends ContextInstance
{
    protected ?string $key = 'role_id';

    protected ?string $projectKey = null;

    public static function getInstance(string $key): static
    {
        $instance = static::instance(suffix: $key);
        is_null($instance->projectKey) && $instance->projectKey = $key;
        return $instance;
    }

    protected function initModels(array $ids): array
    {
        return di()->get(AclRoleactorDao::class)->findByRoleIds($this->projectKey, $ids)->getDictionary();
    }
}
