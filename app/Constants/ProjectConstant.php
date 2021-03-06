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

class ProjectConstant
{
    public const SYS = '$_sys_$';

    public const SORT_KEY_ALL_ISSUES_CNT = 'all_issues_cnt';

    public const SORT_KEY_UNRESOLVED_ISSUES_CNT = 'unresolved_issues_cnt';

    public const SORT_KEY_ASSIGNTOME_ISSUES_CNT = 'assigntome_issues_cnt';

    public const SORT_KEY_ACTIVITY = 'activity';

    public const SORT_KEY_KEY_ASC = 'key_asc';

    public const SORT_KEY_KEY_DESC = 'key_desc';

    public const SORT_KEY_CREATE_TIME_ASC = 'create_time_asc';

    public const SORT_KEY_CREATE_TIME_DESC = 'create_time_desc';

    public const WIKI_FOLDER = 1;

    public const WIKI_CONTENTS = 0;

    /**
     * 项目KEY值，默认增加 p_ 前缀
     */
    public static function formatProjectKey(string $key): string
    {
        if (str_starts_with($key, 'p_')) {
            return $key;
        }

        return sprintf('p_%s', $key);
    }
}
