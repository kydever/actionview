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

use App\Constants\UserConstant;

/**
 * @property int $id
 * @property string $email
 * @property string $first_name
 * @property string $password
 * @property string $last_login
 * @property array $permissions
 * @property int $invalid_flag
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'email', 'first_name', 'password', 'last_login', 'permissions', 'invalid_flag', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'permissions' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'invalid_flag' => 'integer'];

    public function verify(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function isInvalid(): bool
    {
        return $this->invalid_flag === UserConstant::INVALID_FLAG;
    }
}
