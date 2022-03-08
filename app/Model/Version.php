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

use App\Constants\StatusConstant;
/**
 * @property int $id 
 * @property string $project_key 
 * @property string $name 
 * @property int $start_time 
 * @property int $end_time 
 * @property int $released_time 
 * @property string $status 
 * @property string $description 
 * @property array $creator 
 * @property array $modifier 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property-read Project $project 
 */
class Version extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'version';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'name', 'start_time', 'end_time', 'released_time', 'status', 'description', 'creator', 'modifier', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'start_time' => 'integer', 'end_time' => 'integer', 'released_time' => 'integer', 'creator' => 'json', 'modifier' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    public function project()
    {
        return $this->hasOne(Project::class, 'key', 'project_key');
    }
    public function isArchived() : bool
    {
        return $this->status === StatusConstant::STATUS_ARCHIVED;
    }
}