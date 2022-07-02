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
 * @property array $data
 * @property string $event_key
 * @property array $issue
 * @property int $issue_id
 * @property array $user
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Activity extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'activity';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'data', 'event_key', 'issue', 'issue_id', 'user', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'issue_id' => 'integer', 'data' => 'json', 'issue' => 'json', 'user' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
