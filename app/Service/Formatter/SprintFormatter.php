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

use App\Model\Sprint;
use Han\Utils\Service;

class SprintFormatter extends Service
{
    public function base(Sprint $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'project_key' => $model->project_key,
            'no' => $model->no,
            'status' => $model->status,
            'start_time' => $model->start_time,
            'complete_time' => $model->complete_time,
            'real_complete_time' => $model->real_complete_time,
            'description' => $model->description,
            'issues' => $model->issues,
            'origin_issues' => $model->origin_issues,
            'completed_issues' => $model->completed_issues,
            'incompleted_issues' => $model->incompleted_issues,
        ];
    }
}
