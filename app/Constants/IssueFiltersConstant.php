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

class IssueFiltersConstant
{
    public const DEFAULT_ISSUE_FILTERS = [
        ['id' => 'all', 'name' => '全部问题', 'query' => []],
        ['id' => 'unresolved', 'name' => '未解决的', 'query' => ['resolution' => 'Unresolved']],
        ['id' => 'assigned_to_me', 'name' => '分配给我的', 'query' => ['assignee' => 'me', 'resolution' => 'Unresolved']],
        ['id' => 'watched', 'name' => '我关注的', 'query' => ['watcher' => 'me']],
        ['id' => 'reported', 'name' => '我报告的', 'query' => ['reporter' => 'me']],
        ['id' => 'recent_created', 'name' => '最近新建的', 'query' => ['created_at' => '-14d~']],
        ['id' => 'recent_updated', 'name' => '最近更新的', 'query' => ['updated_at' => '-14d~']],
        ['id' => 'recent_resolved', 'name' => '最近解决的', 'query' => ['resolved_at' => '-14d~']],
        ['id' => 'recent_closed', 'name' => '最近关闭的', 'query' => ['closed_at' => '-14d~']],
    ];
}
