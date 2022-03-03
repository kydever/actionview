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
 * @property string $project_key 项目 key
 * @property string $name 看板名称
 * @property string $type 看板类型
 * @property string $description 看板描述
 * @property array $display_fields
 * @property array $columns
 * @property array $filters
 * @property array $query
 * @property array $creator
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Board extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'board';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'name', 'type', 'description', 'display_fields', 'columns', 'filters', 'query', 'creator', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'display_fields' => 'json', 'columns' => 'json', 'filters' => 'json', 'query' => 'json', 'creator' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
