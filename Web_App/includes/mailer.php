<?php

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../assets/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/SMTP.php';

$localMailConfig = __DIR__ . '/mail_config.local.php';
if (is_file($localMailConfig)) {
    require_once $localMailConfig;
}

function makikonekMailSetting(string $constant, string $environment, $default = null)
{
    if (defined($constant)) {
        return constant($constant);
    }

    $value = getenv($environment);
    return $value !== false && $value !== '' ? $value : $default;
}

function sendPasswordResetOtpEmail(
    string $recipientEmail,
    string $recipientName,
    string $otp,
    int $expiresInMinutes = 5
): void {
    $host = (string)makikonekMailSetting('MAIL_HOST', 'MAKIKONEK_MAIL_HOST', 'smtp.gmail.com');
    $port = (int)makikonekMailSetting('MAIL_PORT', 'MAKIKONEK_MAIL_PORT', 587);
    $encryption = strtolower((string)makikonekMailSetting('MAIL_ENCRYPTION', 'MAKIKONEK_MAIL_ENCRYPTION', 'tls'));
    $username = (string)makikonekMailSetting('MAIL_USERNAME', 'MAKIKONEK_MAIL_USERNAME', '');
    $password = (string)makikonekMailSetting('MAIL_PASSWORD', 'MAKIKONEK_MAIL_PASSWORD', '');
    $fromAddress = (string)makikonekMailSetting('MAIL_FROM_ADDRESS', 'MAKIKONEK_MAIL_FROM_ADDRESS', $username);
    $fromName = (string)makikonekMailSetting('MAIL_FROM_NAME', 'MAKIKONEK_MAIL_FROM_NAME', 'MakiKonek Support');

    if ($username === '' || $password === '' || $fromAddress === '') {
        throw new RuntimeException('Mail credentials are not configured.');
    }

    $logoCid = 'makikonek-logo';
    $emailData = require __DIR__ . '/../email_templates/password_reset_otp.php';

    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->Port = $port;
        $mail->SMTPSecure = $encryption === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($recipientEmail, $recipientName);

        $logoPath = __DIR__ . '/../assets/img/email-logo.png';
        if (is_file($logoPath)) {
            $mail->addEmbeddedImage($logoPath, $logoCid, 'email-logo.png');
        }

        $mail->isHTML(true);
        $mail->Subject = $emailData['subject'];
        $mail->Body = $emailData['html'];
        $mail->AltBody = $emailData['text'];
        $mail->send();
    } catch (MailerException $e) {
        throw new RuntimeException('Password reset email could not be sent: ' . $mail->ErrorInfo, 0, $e);
    }
}

