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

use App\Model\AclGroup;
use App\Service\Dao\AclGroupDao;
use Hyperf\Database\Model\Collection;
use Hyperf\Utils\Traits\StaticInstance;

class GroupContext
{
    use StaticInstance;

    /**
     * @var AclGroup[]
     */
    private array $groups;

    public function __construct()
    {
        $this->groups = di()->get(AclGroupDao::class)->all()->getDictionary();
    }

    public function first(int $id): ?AclGroup
    {
        return $this->groups[$id] ?? null;
    }

    public function find(array $ids): Collection
    {
        $items = [];
        foreach ($ids as $id) {
            if ($group = $this->groups[$id] ?? null) {
                $items[] = $group;
            }
        }

        return new Collection($items);
    }
}
