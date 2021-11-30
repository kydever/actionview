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
 * @property string $project_key
 * @property string $name
 * @property int $start_time
 * @property int $end_time
 * @property int $released_time
 * @property string $status
 * @property string $description
 * @property string $creator
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Version extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'version';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'name', 'start_time', 'end_time', 'released_time', 'status', 'description', 'creator', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'start_time' => 'integer', 'end_time' => 'integer', 'released_time' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
