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
use Han\Utils\Service;
use Hyperf\Database\Model\Collection;

class BoardDao extends Service
{
    /**
     * @return Collection<int, Board>
     */
    public function getByProjectKey(string $key): Collection
    {
        return Board::where('project_key', $key)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findById(int $id): ?Board
    {
        return Board::findFromCache($id);
    }

    public function create(string $projectKey, array $columns, array $attributes): ?Board
    {
        $model = new Board();
        $model->project_key = $projectKey;
        $model->query = ['subtask' => true];
        $model->columns = $columns;
        $model->name = $attributes['name'];
        $model->type = $attributes['type'];
        $model->save();

        return $model;
    }
}
