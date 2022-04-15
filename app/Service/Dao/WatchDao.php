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

use App\Model\Watch;
use Han\Utils\Service;

class WatchDao extends Service
{
    public function deleteByIssueId(int $issueId, int $userId)
    {
        return Watch::where('issue_id', $issueId)
            ->whereJsonContains('user->id', $userId)
            ->delete();
    }

    public function create(array $attributes): Watch
    {
        $model = new Watch();
        $model->issue_id = $attributes['id'];
        $model->project_key = $attributes['project_key'];
        $model->user = $attributes['user'];
        $model->save();

        return $model;
    }
}
