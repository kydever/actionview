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
 * @property int $event_id 事件ID
 * @property string $notifications 通知
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConfigEventNotification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'config_event_notifications';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'event_id', 'notifications', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'event_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
