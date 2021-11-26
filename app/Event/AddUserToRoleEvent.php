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
namespace App\Event;

class AddUserToRoleEvent
{
    /**
     * @param array<int> $userIds
     */
    public function __construct(private array $userIds, private string $projectKey)
    {
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function getProjectKey(): string
    {
        return $this->projectKey;
    }
}
