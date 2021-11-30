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
 * @property int $sn
 * @property string $name
 * @property string $abb
 * @property int $screen_id
 * @property int $workflow_id
 * @property string $type
 * @property int $default
 * @property string $description
 * @property int $disabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property ConfigScreen $screen
 */
class ConfigType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'config_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'sn', 'name', 'abb', 'screen_id', 'workflow_id', 'type', 'default', 'description', 'disabled', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'sn' => 'integer', 'screen_id' => 'integer', 'workflow_id' => 'integer', 'default' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'disabled' => 'integer'];

    public function screen()
    {
        return $this->hasOne(ConfigScreen::class, 'id', 'screen_id');
    }
}
