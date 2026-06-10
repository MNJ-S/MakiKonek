<?php

function prgFlashPull(string $scope, string $type = 'success'): string
{
    $message = $_SESSION['_prg_flash'][$scope][$type] ?? '';
    unset($_SESSION['_prg_flash'][$scope][$type]);

    if (empty($_SESSION['_prg_flash'][$scope])) {
        unset($_SESSION['_prg_flash'][$scope]);
    }

    return is_string($message) ? $message : '';
}

function prgRedirect(string $location, string $scope, string $message, string $type = 'success'): void
{
    $_SESSION['_prg_flash'][$scope][$type] = $message;
    header('Location: ' . $location);
    exit();
}
