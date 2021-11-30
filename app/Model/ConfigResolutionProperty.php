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
 * @property array $sequence
 * @property string $default_value
 */
class ConfigResolutionProperty extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'config_resolution_property';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'sequence', 'default_value'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'sequence' => 'json'];
}
