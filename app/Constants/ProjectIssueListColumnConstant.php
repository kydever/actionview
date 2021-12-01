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

class ProjectIssueListColumnConstant
{
    public const DEFAULT_DISPLAY_COLUMNS = [
        ['key' => 'assignee', 'width' => '100'],
        ['key' => 'priority', 'width' => '70'],
        ['key' => 'state', 'width' => '100'],
        ['key' => 'resolution', 'width' => '100'],
    ];
}
