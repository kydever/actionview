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

use App\Constants\Permission;
use App\Constants\ProjectConstant;
use App\Constants\UserConstant;
use App\Service\AclService;
use App\Service\Formatter\UserFormatter;
use App\Service\ProjectAuth;
use Hao\ORMJsonRelation\HasORMJsonRelations;
use Hyperf\Database\Model\Relations\HasMany;

/**
 * @property int $id
 * @property string $email 邮箱
 * @property string $first_name 姓名
 * @property string $password 密码
 * @property string $last_login
 * @property array $permissions
 * @property int $invalid_flag
 * @property string $directory
 * @property string $phone
 * @property string $avatar
 * @property string $department
 * @property string $position
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property AclGroup[]|\Hyperf\Database\Model\Collection $groups
 */
class User extends Model
{
    use HasORMJsonRelations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'email', 'first_name', 'password', 'last_login', 'permissions', 'invalid_flag', 'directory', 'phone', 'avatar', 'department', 'position', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'permissions' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'invalid_flag' => 'integer'];

    public function groups(): HasMany
    {
        return $this->hasManyJsonContains(AclGroup::class, 'users', 'id');
    }

    public function verify(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function isSuperAdmin(): bool
    {
        return UserConstant::isSuperAdmin($this->id);
    }

    public function isInvalid(): bool
    {
        return $this->invalid_flag === UserConstant::INVALID_FLAG;
    }

    public function hasAccess(string $access): bool
    {
        return ($this->permissions[$access] ?? null) === true;
    }

    public function mustContainsAccesses(array $accesses): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        foreach ($accesses as $access) {
            if (! $this->hasAccess($access)) {
                $projectAuth = ProjectAuth::instance();
                if ($projectAuth->getProjectKey() === ProjectConstant::SYS) {
                    return $this->hasAccess(Permission::SYS_ADMIN);
                }
                $isAllowed = di()->get(AclService::class)->hasAccess($this->id, $projectAuth->getCurrent(), $access);
                if ($isAllowed) {
                    continue;
                }
                return false;
            }
        }
        return true;
    }

    public function addPermission(string $permission)
    {
        $permissions = $this->permissions;
        $permissions[$permission] = true;
        $this->permissions = $permissions;
        return $this;
    }

    public function removePermission(string $permission)
    {
        $permissions = $this->permissions;
        $permissions[$permission] = true;
        $this->permissions = false;
        return $this;
    }

    public function getPermissions(): array
    {
        // if ($this->hasAccess(Permission::SYS_ADMIN)) {
        //     return Permission::all();
        // }
        return $this->permissions;
    }

    public function toSmall(): array
    {
        return di()->get(UserFormatter::class)->small($this);
    }
}
