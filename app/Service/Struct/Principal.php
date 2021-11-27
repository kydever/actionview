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
namespace App\Service\Struct;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use App\Service\Dao\UserDao;
use Hyperf\Contract\Arrayable;

/**
 * 负责人.
 */
class Principal implements Arrayable
{
    public function __construct(private string $principal, private User $user)
    {
    }

    public function toArray(): array
    {
        $principal = $this->principal;
        return match ($this->principal) {
            'self', '' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name,
                'email' => $this->user->email,
            ],
            default => value(
                static function () use ($principal) {
                    $model = di()->get(UserDao::class)->first((int) $principal, false);
                    if (empty($model)) {
                        throw new BusinessException(ErrorCode::PROJECT_PRINCIPAL_NOT_EXIST);
                    }
                    return [
                        'id' => $model->id,
                        'name' => $model->first_name,
                        'email' => $model->email,
                    ];
                }
            ),
        };
    }
}
