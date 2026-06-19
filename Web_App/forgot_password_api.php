<?php

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mailer.php';

const RESET_OTP_LIFETIME_MINUTES = 5;
const RESET_VERIFIED_LIFETIME_MINUTES = 15;
const RESET_MAX_ATTEMPTS = 5;
const RESET_REQUEST_LIMIT = 3;

function resetJson(bool $ok, array $data = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['ok' => $ok] + $data);
    exit();
}

function resetMaskEmail(string $email): string
{
    [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
    return mb_substr($local, 0, 1)
        . str_repeat('*', max(2, mb_strlen($local) - 1))
        . '@'
        . $domain;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resetJson(false, ['message' => 'Method not allowed.'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    resetJson(false, ['message' => 'Invalid request body.'], 400);
}

$csrfHeader = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$csrfSession = (string)($_SESSION['forgot_password_csrf'] ?? '');
if ($csrfSession === '' || !hash_equals($csrfSession, $csrfHeader)) {
    resetJson(false, ['message' => 'Your reset session expired. Refresh the page and try again.'], 403);
}

$action = (string)($payload['action'] ?? '');

try {
    mysqli_query(
        $conn,
        "DELETE FROM password_reset_otps
         WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
    );

    if ($action === 'request') {
        $identifier = trim((string)($payload['identifier'] ?? ''));
        if ($identifier === '' || mb_strlen($identifier) > 254) {
            resetJson(false, ['message' => 'Enter a valid email address or username.'], 422);
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT u.user_id, u.username, u.email, p.first_name, p.last_name
             FROM users u
             LEFT JOIN user_profiles p ON p.user_id = u.user_id
             WHERE u.email = ? OR u.username = ?
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'ss', $identifier, $identifier);
        mysqli_stmt_execute($stmt);
        $account = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        // Neutral response prevents exposing whether an account exists.
        if (!$account) {
            resetJson(true, [
                'reset_token' => bin2hex(random_bytes(32)),
                'destination' => str_contains($identifier, '@')
                    ? resetMaskEmail($identifier)
                    : 'the account email',
                'message' => 'If that account exists, a verification code has been sent.',
            ]);
        }

        $userId = (int)$account['user_id'];
        $limitStmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS request_count, MAX(created_at) AS last_request
             FROM password_reset_otps
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        mysqli_stmt_bind_param($limitStmt, 'i', $userId);
        mysqli_stmt_execute($limitStmt);
        $requestStats = mysqli_fetch_assoc(mysqli_stmt_get_result($limitStmt)) ?: [];
        mysqli_stmt_close($limitStmt);

        if ((int)($requestStats['request_count'] ?? 0) >= RESET_REQUEST_LIMIT) {
            resetJson(false, ['message' => 'Too many reset requests. Please wait 15 minutes and try again.'], 429);
        }

        if (!empty($requestStats['last_request'])
            && strtotime((string)$requestStats['last_request']) > time() - 60) {
            resetJson(false, ['message' => 'Please wait one minute before requesting another code.'], 429);
        }

        $resetToken = bin2hex(random_bytes(32));
        $resetTokenHash = hash('sha256', $resetToken);
        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + (RESET_OTP_LIFETIME_MINUTES * 60));
        $requestedIp = mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

        mysqli_begin_transaction($conn);
        try {
            $invalidateStmt = mysqli_prepare(
                $conn,
                "UPDATE password_reset_otps
                 SET consumed_at = NOW()
                 WHERE user_id = ? AND consumed_at IS NULL"
            );
            mysqli_stmt_bind_param($invalidateStmt, 'i', $userId);
            mysqli_stmt_execute($invalidateStmt);
            mysqli_stmt_close($invalidateStmt);

            $insertStmt = mysqli_prepare(
                $conn,
                "INSERT INTO password_reset_otps
                    (user_id, reset_token_hash, otp_hash, expires_at, requested_ip)
                 VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $insertStmt,
                'issss',
                $userId,
                $resetTokenHash,
                $otpHash,
                $expiresAt,
                $requestedIp
            );
            mysqli_stmt_execute($insertStmt);
            $resetId = mysqli_insert_id($conn);
            mysqli_stmt_close($insertStmt);
            mysqli_commit($conn);
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            throw $e;
        }

        $displayName = trim(
            (string)($account['first_name'] ?? '')
            . ' '
            . (string)($account['last_name'] ?? '')
        );
        if ($displayName === '') {
            $displayName = (string)$account['username'];
        }

        try {
            sendPasswordResetOtpEmail(
                (string)$account['email'],
                $displayName,
                $otp,
                RESET_OTP_LIFETIME_MINUTES
            );
        } catch (Throwable $e) {
            $deleteStmt = mysqli_prepare($conn, 'DELETE FROM password_reset_otps WHERE reset_id = ?');
            mysqli_stmt_bind_param($deleteStmt, 'i', $resetId);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);
            error_log($e->getMessage());
            resetJson(false, [
                'message' => 'The verification email could not be sent. Please contact MakiKonek support.',
            ], 503);
        }

        resetJson(true, [
            'reset_token' => $resetToken,
            'destination' => resetMaskEmail((string)$account['email']),
            'message' => 'A six-digit verification code was sent.',
        ]);
    }

    if ($action === 'verify') {
        $resetToken = trim((string)($payload['reset_token'] ?? ''));
        $otp = trim((string)($payload['otp'] ?? ''));
        if (!preg_match('/^[a-f0-9]{64}$/', $resetToken) || !preg_match('/^\d{6}$/', $otp)) {
            resetJson(false, ['message' => 'Enter the complete six-digit code.'], 422);
        }

        $tokenHash = hash('sha256', $resetToken);
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare(
                $conn,
                "SELECT pro.reset_id, pro.user_id, pro.otp_hash, pro.expires_at,
                        pro.consumed_at, pro.attempts, u.username
                 FROM password_reset_otps pro
                 JOIN users u ON u.user_id = pro.user_id
                 WHERE pro.reset_token_hash = ?
                 LIMIT 1
                 FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, 's', $tokenHash);
            mysqli_stmt_execute($stmt);
            $reset = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$reset || $reset['consumed_at'] !== null || strtotime($reset['expires_at']) < time()) {
                mysqli_rollback($conn);
                resetJson(false, ['message' => 'The code is invalid or has expired. Request a new one.'], 422);
            }

            if ((int)$reset['attempts'] >= RESET_MAX_ATTEMPTS) {
                mysqli_rollback($conn);
                resetJson(false, ['message' => 'Too many incorrect attempts. Request a new code.'], 429);
            }

            if (!password_verify($otp, $reset['otp_hash'])) {
                $resetId = (int)$reset['reset_id'];
                $attemptStmt = mysqli_prepare(
                    $conn,
                    'UPDATE password_reset_otps SET attempts = attempts + 1 WHERE reset_id = ?'
                );
                mysqli_stmt_bind_param($attemptStmt, 'i', $resetId);
                mysqli_stmt_execute($attemptStmt);
                mysqli_stmt_close($attemptStmt);
                mysqli_commit($conn);
                resetJson(false, ['message' => 'The code is incorrect. Please try again.'], 422);
            }

            $resetId = (int)$reset['reset_id'];
            $verifyStmt = mysqli_prepare(
                $conn,
                'UPDATE password_reset_otps SET verified_at = NOW() WHERE reset_id = ?'
            );
            mysqli_stmt_bind_param($verifyStmt, 'i', $resetId);
            mysqli_stmt_execute($verifyStmt);
            mysqli_stmt_close($verifyStmt);
            mysqli_commit($conn);

            resetJson(true, [
                'username' => $reset['username'],
                'message' => 'Code verified.',
            ]);
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            throw $e;
        }
    }

    if ($action === 'reset') {
        $resetToken = trim((string)($payload['reset_token'] ?? ''));
        $username = trim((string)($payload['username'] ?? ''));
        $newPassword = (string)($payload['new_password'] ?? '');
        $confirmPassword = (string)($payload['confirm_password'] ?? '');

        if (!preg_match('/^[a-f0-9]{64}$/', $resetToken)) {
            resetJson(false, ['message' => 'Your reset session is invalid. Request another code.'], 422);
        }
        if ($username === '') {
            resetJson(false, ['message' => 'The account username is missing.'], 422);
        }
        if (strlen($newPassword) < 8 || strlen($newPassword) > 72) {
            resetJson(false, ['message' => 'Password must be between 8 and 72 characters.'], 422);
        }
        if ($newPassword !== $confirmPassword) {
            resetJson(false, ['message' => 'The passwords do not match.'], 422);
        }

        $tokenHash = hash('sha256', $resetToken);
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare(
                $conn,
                "SELECT pro.reset_id, pro.user_id, pro.verified_at, pro.consumed_at, u.username
                 FROM password_reset_otps pro
                 JOIN users u ON u.user_id = pro.user_id
                 WHERE pro.reset_token_hash = ?
                 LIMIT 1
                 FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, 's', $tokenHash);
            mysqli_stmt_execute($stmt);
            $reset = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            $verificationExpired = !$reset
                || $reset['verified_at'] === null
                || strtotime($reset['verified_at']) < time() - (RESET_VERIFIED_LIFETIME_MINUTES * 60);

            if ($verificationExpired || $reset['consumed_at'] !== null) {
                mysqli_rollback($conn);
                resetJson(false, ['message' => 'Verification expired. Request another code.'], 422);
            }
            if (!hash_equals((string)$reset['username'], $username)) {
                mysqli_rollback($conn);
                resetJson(false, ['message' => 'The username does not match the verified account.'], 422);
            }

            $storedPassword = makikonekStorePassword($newPassword);
            $userId = (int)$reset['user_id'];
            $updateStmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE user_id = ?');
            mysqli_stmt_bind_param($updateStmt, 'si', $storedPassword, $userId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);

            $consumeStmt = mysqli_prepare(
                $conn,
                'UPDATE password_reset_otps SET consumed_at = NOW() WHERE user_id = ? AND consumed_at IS NULL'
            );
            mysqli_stmt_bind_param($consumeStmt, 'i', $userId);
            mysqli_stmt_execute($consumeStmt);
            mysqli_stmt_close($consumeStmt);
            mysqli_commit($conn);

            resetJson(true, ['message' => 'Password updated successfully.']);
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            throw $e;
        }
    }

    resetJson(false, ['message' => 'Unsupported reset action.'], 400);
} catch (Throwable $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    resetJson(false, ['message' => 'The password reset service is temporarily unavailable.'], 500);
}
