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
 * @property int $entry_id
 * @property int $step_id
 * @property int $previous_id
 * @property int $start_time
 * @property int $action_id
 * @property array $owners
 * @property string $status
 * @property string $comments
 * @property array $caller
 * @property int $finish_time
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OswfHistorystep extends Model
{
    public bool $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'oswf_historystep';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'entry_id', 'step_id', 'previous_id', 'start_time', 'action_id', 'owners', 'status', 'comments', 'caller', 'finish_time', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'entry_id' => 'integer', 'step_id' => 'integer', 'previous_id' => 'integer', 'start_time' => 'integer', 'action_id' => 'integer', 'owners' => 'json', 'caller' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'finish_time' => 'integer'];
}
