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
            'id' => $model->id,
            'wid' => $model->wid,
            'project_key' => $model->project_key,
            'd' => $model->d,
            'del_flag' => $model->del_flag,
            'name' => $model->name,
            'pt' => $model->pt,
            'user' => $model->user,
            'parent' => $model->parent,
            'contents' => $model->contents,
            'version' => $model->version,
            'creator' => $model->creator,
            'editor' => $model->editor,
            'created_at' => $model->created_at->toDateTimeString(),
            'updated_at' => $model->updated_at->toDateTimeString(),
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
