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
 * @property string $name 名称
 * @property string $project_key 项目KEY
 * @property array $principal 负责人
 * @property string $default_assignee 默认指定人
 * @property string $creator 创建者
 * @property string $description 描述
 * @property string $sn 版本
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Module extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'module';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'project_key', 'principal', 'default_assignee', 'creator', 'description', 'sn', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'principal' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}