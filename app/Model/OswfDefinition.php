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
 * @property string $name
 * @property array $latest_modifier
 * @property string $latest_modified_time
 * @property array $state_ids
 * @property array $screen_ids
 * @property int $steps
 * @property array $contents
 * @property string $description 描述
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OswfDefinition extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'oswf_definition';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'name', 'latest_modifier', 'latest_modified_time', 'state_ids', 'screen_ids', 'steps', 'contents', 'description', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'latest_modifier' => 'array', 'state_ids' => 'json', 'screen_ids' => 'json', 'steps' => 'integer', 'contents' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
