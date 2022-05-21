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

use Hyperf\Database\Model\Builder;

/**
 * @property int $id
 * @property string $project_key
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
    protected array $fillable = ['id', 'project_key', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function scopeProjectKey(Builder $query, string $projectKey): Builder
    {
        return $query->where('project_key', $projectKey);
    }

    public function scopeEventKey(Builder $query, string $category): Builder
    {
        return $query->where('event_key', 'like', '%' . $category);
    }
}
