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

use App\Constants\StatusConstant;
use App\Service\Client\IssueSearch;
use Hao\ORMJsonRelation\HasORMJsonRelations;
use Hyperf\Database\Model\Relations\HasOne;

/**
 * @property int $id
 * @property string $project_key
 * @property int $type
 * @property int $parent_id
 * @property int $del_flg
 * @property string $resolution
 * @property array $assignee
 * @property array $reporter
 * @property array $modifier
 * @property int $no
 * @property array $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property User $assigneeModel
 * @property \Hyperf\Database\Model\Collection|Issue[] $children
 * @property Issue $parent
 * @property ConfigType $typeModel
 */
class Issue extends Model implements Searchable
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
    protected $fillable = ['id', 'project_key', 'type', 'parent_id', 'del_flg', 'resolution', 'assignee', 'reporter', 'modifier', 'no', 'data', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'del_flg' => 'integer', 'assignee' => 'json', 'reporter' => 'json', 'modifier' => 'json', 'data' => 'json', 'no' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'type' => 'integer', 'parent_id' => 'integer'];

    public function typeModel()
    {
        return $this->hasOne(ConfigType::class, 'id', 'type');
    }

    public function assigneeModel(): HasOne
    {
        return $this->hasOneInJsonObject(User::class, 'id', 'assignee->id');
    }

    public function parent()
    {
        return $this->hasOne(Issue::class, 'id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Issue::class, 'parent_id', 'id')->where('del_flg', '<>', StatusConstant::DELETED);
    }

    public function pushToSearch(): void
    {
        di()->get(IssueSearch::class)->put($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function entry()
    {
        return $this->hasOneInJsonObject(OswfEntry::class, 'id', 'data->entry_id');
    }
}
