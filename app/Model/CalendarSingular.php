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
 * @property string $date
 * @property string $year
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CalendarSingular extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'calendar_singular';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'date', 'year', 'type', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
