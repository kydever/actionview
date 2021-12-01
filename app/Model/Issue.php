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

use Hao\ORMJsonRelation\HasORMJsonRelations;
use Hyperf\Database\Model\Relations\HasOne;

/**
 * @property int $id
 * @property string $project_key
 * @property int $type
 * @property int $del_flg
 * @property string $resolution
 * @property array $assignee
 * @property array $reporter
 * @property int $no
 * @property array $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property User $assigneeModel
 * @property ConfigType $typeModel
 */
class Issue extends Model
{
    use HasORMJsonRelations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'issue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'project_key', 'type', 'del_flg', 'resolution', 'assignee', 'reporter', 'no', 'data', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'del_flg' => 'integer', 'assignee' => 'json', 'reporter' => 'json', 'data' => 'json', 'no' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'type' => 'integer'];

    public function typeModel()
    {
        return $this->hasOne(ConfigType::class, 'id', 'type');
    }

    public function assigneeModel(): HasOne
    {
        return $this->hasOneInJsonObject(User::class, 'id', 'assignee->id');
    }
}
