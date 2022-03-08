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
 * @property array $properties
 * @property array $mailserver
 * @property array $sysroles
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SysSetting extends Model
{
    public const ALLOW_CREATE_PROJECT = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'sys_setting';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'properties', 'mailserver', 'sysroles', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'properties' => 'json', 'mailserver' => 'json', 'sysroles' => 'json', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function allowCreateProject(): bool
    {
        return ($this->properties['allow_create_project'] ?? null) === self::ALLOW_CREATE_PROJECT;
    }

    public function allowSendEmail(): bool
    {
        $mailServer = $this->mailserver;
        $hasSender = ! empty($mailServer['send']['from']);
        $hasSmtp = ! empty($mailServer['smtp']['host']) && ! empty($mailServer['smtp']['port']) && ! empty($mailServer['smtp']['username']) && ! empty($mailServer['smtp']['password']);
        return $hasSender && $hasSmtp;
    }

    public function getSendPrefix(): string
    {
        $mailServer = $this->mailserver;
        return $mailServer['send']['prefix'] ?? 'ActionView' ?: 'ActionView';
    }

    public function getSmtp(): array
    {
        $smtp = $this->mailserver['smtp'] ?? [];
        return [$smtp['host'], $smtp['port'], $smtp['username'], $smtp['password'], $smtp['encryption'] ?? null];
    }
}
