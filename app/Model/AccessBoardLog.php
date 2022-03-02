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
namespace App\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $project_key
 * @property int $board_id
 * @property string $latest_access_time
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AccessBoardLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'access_board_log';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'user_id', 'project_key', 'board_id', 'latest_access_time', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'user_id' => 'integer', 'board_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
