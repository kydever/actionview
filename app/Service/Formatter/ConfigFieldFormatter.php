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

use App\Model\ConfigField;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ConfigFieldFormatter extends Service
{
    public function base(ConfigField $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'project_key' => $model->project_key,
            'key' => $model->key,
            'type' => $model->type,
            'description' => $model->description,
            'optionValues' => $model->option_values,
            'defaultValue' => $model->default_value,
            'min_value' => $model->min_value,
            'max_value' => $model->max_value,
        ];
    }

    public function formatList(Collection $models)
    {
        $result = [];
        /** @var ConfigField[] $models */
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }
        return $result;
    }
}
