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
namespace App\Service;

use App\Model\CalendarSingular;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class CalendarSingularService extends Service
{
    /**
     * @return Collection<int, CalendarSingular>
     */
    public function getByDays(array $days)
    {
        return CalendarSingular::query()
            ->whereIn('date', $days)
            ->get();
    }
}
