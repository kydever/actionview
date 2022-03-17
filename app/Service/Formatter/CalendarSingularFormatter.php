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

use App\Model\CalendarSingular;
use Han\Utils\Service;

class CalendarSingularFormatter extends Service
{
    public function base(CalendarSingular $model): array
    {
        return [
            'id' => $model->id,
            'date' => $model->date,
            'year' => $model->year,
            'type' => $model->type,
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
