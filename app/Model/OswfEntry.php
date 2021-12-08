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
 * @property int $definition_id
 * @property array $creator
 * @property int $state
 * @property array $propertysets
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Hyperf\Database\Model\Collection|OswfCurrentstep[] $currentSteps
 */
class OswfEntry extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oswf_entry';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'definition_id', 'creator', 'state', 'propertysets', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'definition_id' => 'integer', 'creator' => 'json', 'propertysets' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'state' => 'integer'];

    public function currentSteps()
    {
        return $this->hasMany(OswfCurrentstep::class, 'entry_id', 'id');
    }
}
