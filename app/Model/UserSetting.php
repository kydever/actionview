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
 * @property int $user_id 用户ID
 * @property array $notifications 
 * @property array $favorites 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class UserSetting extends Model
{
    public bool $incrementing = false;
    protected string $primaryKey = 'user_id';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'user_setting';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['user_id', 'notifications', 'favorites', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['user_id' => 'integer', 'notifications' => 'array', 'favorites' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}