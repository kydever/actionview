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
namespace App\Service\Client;

use PHPMailer\PHPMailer\PHPMailer;

class EmailSender
{
    public function __construct(
        private string $host,
        private string $port,
        private string $username,
        private string $password,
        private ?string $encryption = null
    ) {
    }

    public function send(string $subject, string $content, array $emails)
    {
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        if ($this->encryption) {
            $mail->SMTPSecure = $this->encryption;
        }
        $mail->Host = $this->host;
        $mail->Port = $this->port;
        $mail->Username = $this->username;
        $mail->Password = $this->password;
        $mail->setFrom($this->username, 'ActionView');
        $mail->Subject = $subject;
        $mail->msgHTML($content);
        foreach ($emails as $email) {
            $mail->addAddress($email);
        }
        $mail->send();
    }
}
