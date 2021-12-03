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

use App\Model\WikiFavorite;
use Han\Utils\Service;

class WikiFavoriteDao extends Service
{
    /**
     * @param int $wid Wiki ID
     */
    public function first(int $wid, int $userId): ?WikiFavorite
    {
        return WikiFavorite::query()->where('wid', $wid)
            ->where('user_id', $userId)
            ->first();
    }
}
