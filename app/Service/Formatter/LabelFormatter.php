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

use App\Model\Label;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class LabelFormatter extends Service
{
    public function base(Label $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'bgColor' => $model->bgColor,
        ];
    }

    public function formatList(Collection $models)
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }

        return $result;
    }

    public function formatListWithCount(Collection $models, array $labels)
    {
        $result = [];
        /** @var Label $model */
        foreach ($models as $model) {
            $result[] = array_merge($this->base($model), [
                'is_used' => isset($labels[$model->name]),
                'all_cnt' => $labels[$model->name]['all'] ?? 0,
                'unresolved_cnt' => $labels[$model->name]['unresolved'] ?? 0,
            ]);
        }

        return $result;
    }
}
