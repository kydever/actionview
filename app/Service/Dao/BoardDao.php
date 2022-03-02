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
namespace App\Service\Dao;

use App\Model\Board;
use App\Model\Project;
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class BoardDao extends Service
{
    public function getByProjectKey(Project $projectKey): Collection
    {
        return Board::where('project_key', $projectKey)
            ->orderBy('id', 'desc')
            ->get();
    }
}
