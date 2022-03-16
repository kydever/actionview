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

use App\Model\Watch;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class WatchService extends Service
{
    /**
     * @return Collection<int, Watch>
     */
    public function getByProjectKeyAndWatcher(string $projectKey, int $watcher)
    {
        return Watch::where('project_key', $projectKey)
            ->whereJsonContains('user.id', $watcher)
            ->get();
    }
}
