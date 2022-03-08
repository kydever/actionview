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

use App\Event\LabelEvent;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Saved;
use Psr\EventDispatcher\EventDispatcherInterface;
/**
 * @property int $id 
 * @property string $name 名称
 * @property string $bgColor 背景色
 * @property string $project_key 项目key
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Label extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'labels';
    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'bgColor', 'project_key', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    public function saved(Saved $event)
    {
        di()->get(EventDispatcherInterface::class)->dispatch(new LabelEvent($this));
    }
    public function deleted(Deleted $event)
    {
        di()->get(EventDispatcherInterface::class)->dispatch(new LabelEvent($this));
    }
}