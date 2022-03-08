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
 * @property string $project_key 
 * @property string $sequence 
 * @property string $default_value 
 */
class ConfigPriorityProperty extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'config_priority_property';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'sequence', 'default_value'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int'];
}