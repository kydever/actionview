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

use App\Model\Issue;
use Han\Utils\Service;

class IssueFormatter extends Service
{
    public function base(Issue $model)
    {
        $result = [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'type' => (string) $model->type,
            'del_flg' => $model->del_flg,
            'resolution' => $model->resolution,
            'assignee' => $model->assignee,
            'reporter' => $model->reporter,
            'no' => $model->no,
            'created_at' => $model->created_at->getTimestamp(),
        ];

        return array_replace($model->data, $result);
    }

    public function formatList($models)
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->base($model);
        }
        return $result;
    }
}
