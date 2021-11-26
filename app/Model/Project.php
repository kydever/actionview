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

use JetBrains\PhpStorm\ArrayShape;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property array $principal
 * @property int $category
 * @property string $description
 * @property array $creator
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Project extends Model
{
    public const ACTIVE = 'active';

    public const ALL = 'all';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'key', 'principal', 'category', 'description', 'creator', 'status', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'principal' => 'json', 'category' => 'integer', 'creator' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    #[ArrayShape(['id' => 'int', 'name' => 'string', 'email' => 'string'])]
    public function getPrincipal(): array
    {
        return $this->principal;
    }
}
