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
 * @property string $project_key 项目key
 * @property int $no 
 * @property string $name 名称
 * @property string $status 
 * @property int $start_time 开始时间
 * @property int $complete_time 完成时间
 * @property string $description 描述
 * @property int $real_complete_time 真实完成时间
 * @property array $issues 
 * @property array $origin_issues 
 * @property array $completed_issues 
 * @property array $incompleted_issues 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Sprint extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'sprint';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'no', 'name', 'status', 'start_time', 'complete_time', 'description', 'real_complete_time', 'issues', 'origin_issues', 'completed_issues', 'incompleted_issues', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'issues' => 'json', 'origin_issues' => 'json', 'completed_issues' => 'json', 'incompleted_issues' => 'json', 'no' => 'integer', 'start_time' => 'integer', 'complete_time' => 'integer', 'real_complete_time' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}