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

use App\Model\Epic;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class EpicFormatter extends Service
{
    public function base(Epic $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'bgColor' => $model->bgColor,
            'description' => $model->description,
            'sn' => $model->sn,
            'created_at' => (string) $model->created_at,
            'updated_at' => (string) $model->updated_at,
        ];
    }

    public function formatList(Collection $models)
    {
        $results = [];
        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
