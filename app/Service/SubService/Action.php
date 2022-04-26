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
namespace App\Service\SubService;

use Han\Utils\Service;

class Action extends Service
{
    public function match(string $message): ?array
    {
        $pattern = '/([\x7f-\xff\w\d]+) https?\:\/\/([\w+\.\:]+)\/actionview\/project\/(\w+)\/issue\?no=(\d+)/';

        preg_match_all($pattern, $message, $matched);

        if (count($matched) !== 5) {
            return null;
        }

        $result = [];
        foreach ($matched[2] as $i => $item) {
            $result[] = ['action' => $action, 'project' => $matched[3][$i], 'no' => $matched[4][$i]];
        }

        return $result;
    }
}
