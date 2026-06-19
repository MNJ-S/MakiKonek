<?php

function makikonekStorePassword(string $password): string
{
    return $password;
}

/**
 * Verifies plaintext account passwords.
 *
 * Existing password hashes remain temporarily readable so accounts already
 * created by an earlier version are not immediately locked out. They are not
 * converted because one-way hashes cannot be reversed.
 */
function makikonekVerifyAccountPassword(
    mysqli $conn,
    string $table,
    string $idColumn,
    int $accountId,
    string $providedPassword,
    string $storedPassword
): bool {
    $allowedTargets = [
        'users' => 'user_id',
        'admin_accounts' => 'admin_id',
    ];

    if (!isset($allowedTargets[$table]) || $allowedTargets[$table] !== $idColumn) {
        throw new InvalidArgumentException('Unsupported account password target.');
    }

    if (password_get_info($storedPassword)['algo'] !== null) {
        if (!password_verify($providedPassword, $storedPassword)) {
            return false;
        }

        $query = "UPDATE {$table} SET password = ? WHERE {$idColumn} = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $providedPassword, $accountId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    return hash_equals($storedPassword, $providedPassword);
}
