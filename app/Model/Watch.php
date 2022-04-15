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
 * @property int $issue_id
 * @property string $project_key
 * @property array $user
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Watch extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'watch';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'issue_id', 'project_key', 'user', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'user' => 'json', 'issue_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
