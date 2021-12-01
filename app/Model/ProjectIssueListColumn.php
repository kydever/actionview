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
 * @property string $project_key 项目key
 * @property array $column_keys
 * @property array $columns
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ProjectIssueListColumn extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_issue_list_columns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'column_keys', 'columns', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'columns' => 'json', 'column_keys' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
