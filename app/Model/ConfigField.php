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
 * @property string $project_key 项目KEY
 * @property string $name 字段名
 * @property string $key 字段KEY
 * @property string $type 字段类型
 * @property string $description 字段描述
 * @property string $option_values 选项值
 * @property string $default_value 默认值
 * @property string $min_value 默认最小值
 * @property string $max_value 默认最大值
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConfigField extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'config_field';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'name', 'key', 'type', 'description', 'option_values', 'default_value', 'min_value', 'max_value', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
