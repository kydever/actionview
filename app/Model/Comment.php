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
 * @property string $contents 评论内容
 * @property array $at_who at用户列表
 * @property int $issue_id
 * @property array $creator 创建者
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Comment extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'comments';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'contents', 'at_who', 'issue_id', 'creator', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'issue_id' => 'integer', 'at_who' => 'json', 'creator' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
