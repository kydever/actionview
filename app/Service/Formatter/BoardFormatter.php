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

use App\Model\Board;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class BoardFormatter extends Service
{
    public function base(Board $model)
    {
        return [
            'id' => (string) $model->id,
            'project_key' => $model->project_key,
            'name' => $model->name,
            'type' => $model->type,
            'description' => $model->description,
            'display_fields' => $model->display_fields,
            'columns' => $model->columns,
            'filters' => $model->filters,
            'query' => $model->query,
            'creator' => $model->creator,
            'created_at' => (string) $model->created_at,
            'updated_at' => (string) $model->updated_at,
        ];
    }

    /**
     * @param array|Collection<int, Board> $models
     */
    public function listFormat($models)
    {
        $results = [];
        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
