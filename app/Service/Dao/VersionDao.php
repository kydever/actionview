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

use App\Model\Version;
use Han\Utils\Service;

class VersionDao extends Service
{
    /**
     * @return \Hyperf\Database\Model\Collection|Version[]
     */
    public function findByProjectKey(string $key)
    {
        return Version::query()->where(['project_key' => $key])
            ->orderBy('status', 'desc')
            ->orderBy('released_time', 'desc')
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function index(string $key, int $offset, int $limit): array
    {
        $query = Version::query()
            ->where(['project_key' => $key])
            ->orderBy('status', 'desc')
            ->orderBy('released_time', 'desc')
            ->orderBy('end_time', 'desc')
            ->orderBy('created_at', 'desc');
        return $this->factory->model->pagination($query, $offset, $limit);
    }
}
