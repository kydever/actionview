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

use App\Model\Issue;

class IssueEvent
{
    public function __construct(private Issue $issue)
    {
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }
}
