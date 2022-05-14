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
use Hyperf\Database\Model\Events\Saved;
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
 * @property array $watchers 关注者
 * @property int $no
 * @property array $data
 * @property int $comments_num 评论数
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property array $attachments 附件
 * @property ConfigType $typeModel
 * @property User $assigneeModel
 * @property Issue $parent
 * @property \Hyperf\Database\Model\Collection|Issue[] $children
 * @property OswfEntry $entry
 */
class Issue extends Model implements Searchable
{
    use HasORMJsonRelations;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'issue';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'project_key', 'type', 'parent_id', 'del_flg', 'resolution', 'assignee', 'reporter', 'modifier', 'watchers', 'no', 'data', 'comments_num', 'created_at', 'updated_at', 'attachments'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'del_flg' => 'integer', 'assignee' => 'json', 'reporter' => 'json', 'modifier' => 'json', 'data' => 'json', 'no' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'type' => 'integer', 'parent_id' => 'integer', 'attachments' => 'json', 'watchers' => 'json', 'comments_num' => 'integer'];

    public function saved(Saved $event)
    {
        defer(function () {
            $this->pushToSearch();
        });
    }

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

    public function entry(): HasOne
    {
        return $this->hasOneInJsonObject(OswfEntry::class, 'id', 'data->entry_id');
    }

    public function getData(): array
    {
        $stringArray = ['state'];
        $result = $this->data;
        foreach ($stringArray as $key) {
            if (isset($result[$key])) {
                $result[$key] = (string) $result[$key];
            }
        }
        return format_id_to_string($result);
    }
}
