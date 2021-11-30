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
    private bool $changed = true;

    private string $principal;

    public function __construct(mixed $principal, private ?User $user = null)
    {
        if ($principal === null) {
            $this->changed = false;
        }

        $this->principal = (string) $principal;
    }

    public function isChanged(): bool
    {
        return $this->changed;
    }

    public function getPrincipal(): string
    {
        return $this->principal;
    }

    public function toArray(): array
    {
        $principal = $this->principal;
        return match ($this->principal) {
            'self', '' => value(function () {
                if ($this->user === null) {
                    throw new BusinessException(ErrorCode::PROJECT_PRINCIPAL_CANNOT_EMPTY);
                }

                return [
                    'id' => $this->user->id,
                    'name' => $this->user->first_name,
                    'email' => $this->user->email,
                ];
            }),
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
