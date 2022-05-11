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
 * @property int $issue_id
 * @property string $operation
 * @property int $operated_at
 * @property string $operator
 * @property string $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class IssueHistory extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'issue_history';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'issue_id', 'operation', 'operated_at', 'operator', 'data', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'issue_id' => 'integer', 'operated_at' => 'integer', 'operator' => 'array', 'data' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
