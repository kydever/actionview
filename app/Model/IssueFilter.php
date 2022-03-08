<?php

declare (strict_types=1);
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
 * @property string $name 名称
 * @property array $query 
 * @property string $scope 
 * @property array $creator 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class IssueFilter extends Model
{
    public bool $incrementing = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'issue_filters';
    protected string $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'name', 'query', 'scope', 'creator', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'creator' => 'json', 'query' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}