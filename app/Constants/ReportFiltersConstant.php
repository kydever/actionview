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
namespace App\Constants;

class ReportFiltersConstant
{
    public const DEFAULT_REPORT_FILTERS = [
        'issues' => [
            ['id' => 'all_by_type', 'name' => '全部问题/按类型', 'query' => ['stat_x' => 'type', 'stat_y' => 'type']],
            ['id' => 'unresolved_by_assignee', 'name' => '未解决的/按负责人', 'query' => ['stat_x' => 'assignee', 'stat_y' => 'assignee', 'resolution' => 'Unresolved']],
            ['id' => 'unresolved_by_priority', 'name' => '未解决的/按优先级', 'query' => ['stat_x' => 'priority', 'stat_y' => 'priority', 'resolution' => 'Unresolved']],
            ['id' => 'unresolved_by_module', 'name' => '未解决的/按模块', 'query' => ['stat_x' => 'module', 'stat_y' => 'module', 'resolution' => 'Unresolved']],
        ],
        'worklog' => [
            ['id' => 'all', 'name' => '全部填报', 'query' => []],
            ['id' => 'in_one_month', 'name' => '过去一个月的', 'query' => ['recorded_at' => '-30d~']],
            ['id' => 'active_sprint', 'name' => '当前活动Sprint', 'query' => []],
            ['id' => 'latest_completed_sprint', 'name' => '最近已完成Sprint', 'query' => []],
            // [ 'id' => 'will_release_version', 'name' => '最近要发布版本', 'query' => [] ],
            // [ 'id' => 'latest_released_version', 'name' => '最近已发布版本', 'query' => [] ],
        ],
        'timetracks' => [
            ['id' => 'all', 'name' => '全部问题', 'query' => []],
            ['id' => 'unresolved', 'name' => '未解决的', 'query' => ['resolution' => 'Unresolved']],
            ['id' => 'active_sprint', 'name' => '当前活动Sprint', 'query' => []],
            ['id' => 'latest_completed_sprint', 'name' => '最近已完成Sprint', 'query' => []],
            // [ 'id' => 'will_release_version', 'name' => '最近要发布版本', 'query' => [] ],
            // [ 'id' => 'latest_released_version', 'name' => '最近已发布版本', 'query' => [] ],
        ],
        'regressions' => [
            ['id' => 'all', 'name' => '已解决问题', 'query' => []],
            ['id' => 'active_sprint', 'name' => '当前活动Sprint', 'query' => []],
            ['id' => 'latest_completed_sprint', 'name' => '最近已完成Sprint', 'query' => []],
        ],
        'trend' => [
            ['id' => 'day_in_one_month', 'name' => '问题每日变化趋势', 'query' => ['stat_time' => '-30d~']],
            ['id' => 'week_in_two_months', 'name' => '问题每周变化趋势', 'query' => ['stat_time' => '-60d~', 'interval' => 'week']],
        ],
    ];

    public const MODE_MENU = ['issues', 'trend', 'worklog', 'timetracks', 'regressions', 'others'];
}
