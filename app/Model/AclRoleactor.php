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
 * @property int $role_id 
 * @property string $project_key 
 * @property array $user_ids 
 * @property array $group_ids 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class AclRoleactor extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'acl_roleactor';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'role_id', 'project_key', 'user_ids', 'group_ids', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'role_id' => 'integer', 'user_ids' => 'json', 'group_ids' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}