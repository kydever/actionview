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
 * @property int $wid
 * @property int $user_id
 * @property array $user
 */
class WikiFavorite extends Model
{
    public bool $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'wiki_favorites';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'wid', 'user_id', 'user'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'int', 'wid' => 'integer', 'user_id' => 'integer', 'user' => 'json'];
}
