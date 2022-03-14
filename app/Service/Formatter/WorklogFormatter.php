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

use App\Model\Worklog;
use Han\Utils\Service;

class WorklogFormatter extends Service
{
    public function base(Worklog $model): array
    {
        return [
            'id' => $model->id,
            'issue_id' => $model->issue_id,
            'project_key' => $model->project_key,
            'recorder' => $model->recorder,
            'recorded_at' => $model->recorded_at,
            'started_at' => $model->started_at,
            'spend' => $model->spend,
            'spend_m' => $model->spend_m,
            'adjust_type' => $model->adjust_type,
            'comments' => $model->comments,
            'leave_estimate' => $model->leave_estimate,
            'cut' => $model->cut,
            'edited_flag' => $model->edited_flag,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }

    public function formatList($models): array
    {
        $results = [];

        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
