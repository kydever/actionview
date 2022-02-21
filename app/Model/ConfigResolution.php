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
 * @property string $key KEY
 * @property string $name 名字
 * @property string $sn 版本
 * @property int $default
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConfigResolution extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'config_resolution';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'key', 'name', 'sn', 'default', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'default' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
