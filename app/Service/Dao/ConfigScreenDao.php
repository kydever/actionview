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
namespace App\Service\Dao;

use App\Model\ConfigScreen;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ConfigScreenDao extends Service
{
    /**
     * @return Collection<int, ConfigScreen>
     */
    public function findMany(array $ids)
    {
        return ConfigScreen::findManyFromCache($ids);
    }
}
