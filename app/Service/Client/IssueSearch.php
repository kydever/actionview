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
namespace App\Service\Client;

use Han\Utils\ElasticSearch;

class IssueSearch extends ElasticSearch
{
    public function mapping(): array
    {
        return [
            'id' => ['type' => 'long'],
            'project_key' => ['type' => 'keyword'],
            'type' => ['type' => 'long'],
            'parent_id' => ['type' => 'long'],
            'del_flg' => ['type' => 'byte'],
            'resolution' => ['type' => 'keyword'],
            'assignee' => [
                'properties' => [
                    'id' => ['type' => 'long'],
                    'name' => ['type' => 'text'],
                    'email' => ['type' => 'text'],
                ],
            ],
            'reporter' => [
                'properties' => [
                    'id' => ['type' => 'long'],
                    'name' => ['type' => 'text'],
                    'email' => ['type' => 'text'],
                ],
            ],
            'modifier' => [
                'properties' => [
                    'id' => ['type' => 'long'],
                    'name' => ['type' => 'text'],
                    'email' => ['type' => 'text'],
                ],
            ],
            // 'data' => ['type' => 'array']
        ];
    }

    public function index(): string
    {
        return 'actionview_issue';
    }

    public function type(): string
    {
        return 'doc';
    }

    protected function check($data): bool
    {
        return true;
    }
}
