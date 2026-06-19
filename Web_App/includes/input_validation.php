<?php

function inputIsName(string $value, bool $allowEmpty = false): bool
{
    $value = trim($value);
    return ($allowEmpty && $value === '') || (bool)preg_match('/^[\p{L} .-]+$/u', $value);
}

function inputIsPhone(string $value, bool $allowEmpty = false): bool
{
    $value = trim($value);
    if ($allowEmpty && $value === '') return true;
    return (bool)preg_match('/^09\d{9}$/', $value);
}

function inputIsInteger(string $value, int $min = 0, ?int $max = null, bool $allowEmpty = false): bool
{
    $value = trim($value);
    if ($allowEmpty && $value === '') return true;
    if (!preg_match('/^\d+$/', $value)) return false;
    $number = (int)$value;
    return $number >= $min && ($max === null || $number <= $max);
}

function inputIsNumericId(string $value, bool $allowEmpty = true, ?int $maxDigits = null): bool
{
    $value = trim($value);
    if ($allowEmpty && $value === '') return true;
    return (bool)preg_match('/^\d+$/', $value) && ($maxDigits === null || strlen($value) <= $maxDigits);
}

function inputIsVoterId(string $value, bool $allowEmpty = true): bool
{
    $value = trim($value);
    return ($allowEmpty && $value === '')
        || (bool)preg_match('/^(?:\d{1,12}|\d{4}-\d{6}-\d-\d{3}-[A-Za-z]\d{2})$/', $value);
}

function inputIsDate(string $value): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function inputIsTime(string $value): bool
{
    return (bool)preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value);
}

function inputLength(string $value, int $max): bool
{
    return function_exists('mb_strlen') ? mb_strlen($value) <= $max : strlen($value) <= $max;
}

function inputUploadedFileError(array $file, array $extensions, array $mimeTypes, int $maxBytes = 5242880): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return 'The selected file could not be uploaded.';
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) return 'The selected file must be no larger than 5 MB.';
    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, $extensions, true)) return 'The selected file type is not allowed.';
    $mime = function_exists('finfo_open') ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
    if ($mime !== '' && !in_array($mime, $mimeTypes, true)) return 'The selected file content does not match an allowed file type.';
    return null;
}
