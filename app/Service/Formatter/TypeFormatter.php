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

use App\Model\ConfigType;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class TypeFormatter extends Service
{
    public function base(ConfigType $model)
    {
        return [
            'id' => $model->id,
            'sn' => $model->sn,
            'name' => $model->name,
            'abb' => $model->abb,
            'type' => $model->type,
            'default' => $model->default,
            'description' => $model->description,
            'disabled' => $model->disabled,
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
