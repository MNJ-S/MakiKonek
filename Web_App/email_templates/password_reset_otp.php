<?php

/**
 * Edit this file to change the password-reset email design.
 *
 * Available values:
 * - $recipientName
 * - $otp
 * - $expiresInMinutes
 * - $logoCid
 */

$safeName = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
$safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
$safeMinutes = (int)$expiresInMinutes;
$safeLogoCid = htmlspecialchars($logoCid, ENT_QUOTES, 'UTF-8');

return [
    'subject' => 'MakiKonek password reset code',
    'html' => <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MakiKonek Password Reset</title>
</head>
<body style="margin:0;background:#f3f7f4;font-family:Arial,sans-serif;color:#24332a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 12px;background:#f3f7f4;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #dce8df;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:26px 32px;background:#087b34;text-align:center;">
                            <img src="cid:{$safeLogoCid}" width="170" alt="MakiKonek" style="display:inline-block;max-width:170px;height:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px 32px;">
                            <h1 style="margin:0 0 14px;font-size:24px;color:#153c25;">Password reset request</h1>
                            <p style="margin:0 0 18px;line-height:1.6;">Hello {$safeName},</p>
                            <p style="margin:0 0 22px;line-height:1.6;">Use the verification code below to reset your MakiKonek account password.</p>
                            <div style="padding:18px;border-radius:10px;background:#edf8f0;text-align:center;font-size:34px;font-weight:800;letter-spacing:10px;color:#087b34;">{$safeOtp}</div>
                            <p style="margin:22px 0 0;line-height:1.6;color:#627168;">This code expires in {$safeMinutes} minutes. If you did not request a password reset, you can safely ignore this email.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background:#f8fbf9;text-align:center;font-size:12px;color:#7c8a81;">
                            Barangay Makiling · MakiKonek Support
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML,
    'text' => "Hello {$recipientName},\n\nYour MakiKonek password reset code is: {$otp}\n\nThis code expires in {$expiresInMinutes} minutes. If you did not request this reset, ignore this email.",
];

