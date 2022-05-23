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

use App\Model\ReportFilter;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class ReportFilterFormatter extends Service
{
    public function base(ReportFilter $model): array
    {
        return [
            'id' => $model->id,
            'project_key' => $model->project_key,
            'mode' => $model->mode,
            'user' => $model->user_id,
            'filters' => $model->filters,
        ];
    }

    public function foramtList(Collection $models): array
    {
        $results = [];
        foreach ($models as $model) {
            $results[] = $this->base($model);
        }

        return $results;
    }
}
