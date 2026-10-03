<?php

declare(strict_types=1);

namespace App\Helpers;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public static function sendPasswordReset(string $to, string $name, string $resetUrl, string $accountType = 'account'): void
    {
        $mailConfig = config()['mail'] ?? [];
        $host = trim((string) ($mailConfig['host'] ?? ''));
        $from = trim((string) ($mailConfig['from_address'] ?? ''));

        if ($host === '' || $from === '') {
            throw new \RuntimeException('Password reset email is not configured. Set MAIL_HOST and MAIL_FROM_ADDRESS.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int) ($mailConfig['port'] ?? 587);
        $mail->SMTPAuth = trim((string) ($mailConfig['username'] ?? '')) !== '';
        $mail->Username = (string) ($mailConfig['username'] ?? '');
        $mail->Password = (string) ($mailConfig['password'] ?? '');

        $encryption = strtolower(trim((string) ($mailConfig['encryption'] ?? 'tls')));
        if ($encryption === 'ssl' || $encryption === 'smtps') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls' || $encryption === 'starttls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($from, (string) ($mailConfig['from_name'] ?? 'SocietyOS'));
        $mail->addAddress($to, $name);
        $mail->isHTML(true);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $label = $accountType === 'platform' ? 'platform administrator account' : 'SocietyOS account';

        $mail->Subject = 'SocietyOS password reset request';
        $mail->Body = <<<HTML
<!doctype html>
<html>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#222">
    <p>Hello {$safeName},</p>
    <p>We received a request to reset the password for your {$label}.</p>
    <p><a href="{$safeUrl}" style="display:inline-block;padding:10px 18px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:6px">Reset Password</a></p>
    <p>This link expires in 60 minutes and can be used only once.</p>
    <p>If you did not request this, you can safely ignore this email.</p>
    <p>Regards,<br>SocietyOS</p>
</body>
</html>
HTML;
        $mail->AltBody = "Hello {$name},

Reset your {$label} password using this link:
{$resetUrl}

The link expires in 60 minutes and can be used only once. If you did not request this, ignore this email.

SocietyOS";

        try {
            $mail->send();
        } catch (MailException $e) {
            throw new \RuntimeException('Unable to send password reset email.', 0, $e);
        }
    }
}
