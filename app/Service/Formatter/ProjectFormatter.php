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

use App\Model\Project;
use Han\Utils\Service;

class ProjectFormatter extends Service
{
    public function small(Project $model): array
    {
        return [
            'key' => $model->key,
            'name' => $model->name,
            'principal' => $model->principal,
        ];
    }

    public function base(Project $model)
    {
        $result = [
            'id' => $model->id,
            'key' => $model->key,
            'name' => $model->name,
            'principal' => format_id_to_string($model->principal),
            'category' => $model->category,
            'description' => $model->description,
            'creator' => format_id_to_string($model->creator),
            'status' => $model->status,
            'created_at' => $model->created_at->toDateTimeString(),
            'updated_at' => $model->created_at->toDateTimeString(),
        ];

        $result['principal']['nameAndEmail'] = sprintf('%s(%s)', $model->principal['name'] ?? '未知', $model->principal['email'] ?? '未知');
        return $result;
    }

    /**
     * @param Project[] $models
     */
    public function formatList($models): array
    {
        $result = [];
        foreach ($models as $model) {
            $item = $this->base($model);
            $result[] = $item;
        }

        return $result;
    }
}
