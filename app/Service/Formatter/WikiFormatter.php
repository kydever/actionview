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

use App\Model\Wiki;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class WikiFormatter extends Service
{
    public function base(Wiki $model)
    {
        return [
            'id' => (string) $model->id,
            'project_key' => $model->project_key,
            'd' => $model->d,
            'del_flag' => $model->del_flag,
            'name' => $model->name,
            'pt' => $model->pt,
            'parent' => (string) $model->parent,
            'version' => $model->version,
            'creator' => $model->creator,
            'editor' => $model->editor,
            'checkin' => $model->checkin,
            'created_at' => $model->created_at->timestamp,
            'updated_at' => $model->updated_at->timestamp,
        ];
    }

    /**
     * @param Wiki[] $models
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
