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
 * @property string $project_key 项目键值
 * @property string $recorder
 * @property int $recorded_at
 * @property int $started_at 开始日期
 * @property string $spend 总耗费时间
 * @property int $spend_m
 * @property int $adjust_type
 * @property string $comments 备注
 * @property string $leave_estimate
 * @property string $cut
 * @property int $edited_flag
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Worklog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'worklog';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'issue_id', 'project_key', 'recorder', 'recorded_at', 'started_at', 'spend', 'spend_m', 'adjust_type', 'comments', 'leave_estimate', 'cut', 'edited_flag', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'issue_id' => 'integer', 'recorded_at' => 'integer', 'started_at' => 'integer', 'spend_m' => 'integer', 'adjust_type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'edited_flag' => 'integer'];

    public function setRecorderAttribute($recorder)
    {
        $this->attributes['recorder'] = json_encode($recorder);
    }

    public function getRecorderAttribute($recorder)
    {
        return json_decode($recorder, true);
    }
}
