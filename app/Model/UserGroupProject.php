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
 * @property int $ug_id
 * @property int $type
 * @property int $link_count
 */
class UserGroupProject extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_group_project';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'ug_id', 'type', 'link_count'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'ug_id' => 'integer', 'type' => 'integer', 'link_count' => 'integer'];
}
