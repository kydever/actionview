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
 * @property int $del_flg
 * @property string $resolution
 * @property string $assignee
 */
class Issue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'issue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'del_flg', 'resolution', 'assignee'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'del_flg' => 'integer'];
}
