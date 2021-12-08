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
namespace App\Service\Formatter;

use App\Model\WikiFavorite;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class WikiFavoriteFormatter extends Service
{
    public function base(WikiFavorite $model)
    {
        return [
            'id' => $model->id,
            'wid' => $model->wid,
            'user_id' => $model->user_id,
            'user' => $model->user,
        ];
    }

    /**
     * @param WikiFavorite[] $models
     */
    public function formatList(Collection $models): array
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }

        return $result;
    }
}
