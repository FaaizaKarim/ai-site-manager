<?php
// config/mail.php — Gmail SMTP via PHPMailer (manual install, no Composer)

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function sendResetEmail(string $toEmail, string $resetLink): bool
{
    $mailUser = $_ENV['MAIL_USER'] ?? '';
    $mailPass = $_ENV['MAIL_PASS'] ?? '';

    if ($mailUser === '' || $mailPass === '') {
        error_log('Password reset email failed: MAIL_USER or MAIL_PASS not set in .env');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUser;
        $mail->Password   = $mailPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;

        $mail->setFrom($mailUser, 'AI Site Manager');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Reset your AI Site Manager password';

        $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

        $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:'Segoe UI',system-ui,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" style="max-width:520px;background:#ffffff;border-radius:12px;padding:40px 32px;">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 8px;font-size:22px;color:#111827;">Password reset</h1>
                            <p style="margin:0 0 24px;font-size:15px;color:#6b7280;line-height:1.6;">
                                You requested a password reset for your AI Site Manager account.
                                Click the button below to choose a new password. This link expires in 1 hour.
                            </p>
                            <p style="margin:0 0 24px;text-align:center;">
                                <a href="{$safeLink}"
                                   style="display:inline-block;background:#6366f1;color:#ffffff;text-decoration:none;
                                          padding:12px 28px;border-radius:8px;font-weight:600;font-size:15px;">
                                    Reset Password
                                </a>
                            </p>
                            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;">
                                If you did not request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        $mail->AltBody = "Reset your AI Site Manager password:\n\n{$resetLink}\n\n"
            . "This link expires in 1 hour. If you did not request this, ignore this email.";

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('Password reset email failed: ' . $mail->ErrorInfo);
        return false;
    }
}
