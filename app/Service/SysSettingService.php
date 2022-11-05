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
namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\Client\EmailSender;
use App\Service\Dao\SysSettingDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\SysSettingFormatter;
use Han\Utils\Service;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Throwable;

class SysSettingService extends Service
{
    #[Inject]
    protected SysSettingDao $dao;

    #[Inject()]
    protected SysSettingFormatter $formatter;

    /**
     * @param $input = [
     *     'properties' => 'array',
     *     'smtp' => 'array',
     *     'smtp.password' => 'string',
     *     'mail_send' => 'array',
     *     'sysroles' => 'array',
     * ]
     */
    public function update(array $input)
    {
        $properties = $input['properties'] ?? [];
        $smtp = $input['smtp'] ?? [];
        $mailSend = $input['mail_send'] ?? [];
        $roles = $input['sysroles'] ?? [];

        $model = $this->dao->first();

        if (! empty($properties)) {
            $model->properties = $properties;
        }

        $mailServer = $model->mailserver ?? [];
        if (! empty($smtp)) {
            $smtp['password'] = $smtp['password'] ?? $mailserver['smtp']['password'] ?? '';
            $mailServer['smtp'] = $smtp;
        }

        if (! empty($mailSend)) {
            $mailServer['send'] = $mailSend;
        }

        $model->mailserver = $mailServer;

        $addedUserIds = [];
        $deletedUserIds = [];
        if (! empty($roles)) {
            $sysAdminIds = array_column($model->sysroles['sys_admin'] ?? [], 'id');
            $newSysAdminIds = array_column($roles['sys_admin'] ?? [], 'id');

            $addedUserIds = array_diff($newSysAdminIds, $sysAdminIds);
            $deletedUserIds = array_diff($sysAdminIds, $newSysAdminIds);

            $model->sysroles = $roles;
        }

        Db::beginTransaction();
        try {
            $model->save();

            $this->handleUserPermission('sys_admin', $addedUserIds, $deletedUserIds);
            Db::commit();
        } catch (Throwable $exception) {
            Db::rollBack();
            throw $exception;
        }

        return $this->formatter->base($model);
    }

    public function handleUserPermission(string $permission, array $addedUserIds, array $deletedUserIds)
    {
        if ($addedUserIds) {
            $users = di()->get(UserDao::class)->findMany($addedUserIds);
            foreach ($users as $user) {
                $user->addPermission($permission);
            }
        }

        if ($deletedUserIds) {
            $users = di()->get(UserDao::class)->findMany($deletedUserIds);
            foreach ($users as $user) {
                $user->removePermission($permission);
            }
        }
    }

    /**
     * @param $input = [
     *     'to' => '',
     *     'subject' => '',
     *     'contents' => '',
     * ]
     */
    public function sendTestMail(array $input)
    {
        $to = $input['to'];
        $subject = $input['subject'];
        $contents = $input['contents'] ?? '';

        $setting = $this->dao->first();
        if (! $setting->allowSendEmail()) {
            throw new BusinessException(ErrorCode::MAIL_INVALID);
        }

        $subject = '[' . $setting->getSendPrefix() . ']' . $subject;

        try {
            $client = new EmailSender(...$setting->getSmtp());
            $client->send($subject, $contents, [$to]);
        } catch (Throwable $e) {
            $this->logger->error((string) $e);
            throw new BusinessException(ErrorCode::MAIL_SEND_FAILED, $e->getMessage());
        }

        return true;
    }
}
