<?php

declare(strict_types=1);

const PATIENTS_TABLE = 'patients';
const ADMINS_TABLE = 'admins';

function hashPassword(string $plainPassword): string
{
    return password_hash($plainPassword, PASSWORD_DEFAULT);
}

function verifyPassword(string $plainPassword, ?string $hash): bool
{
    if ($hash === null || trim($hash) === '') {
        password_verify($plainPassword, unloginableAccountHash());

        return false;
    }

    return password_verify($plainPassword, $hash);
}

function unloginableAccountHash(): string
{
    static $hash;

    return $hash ??= password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
}

function refreshPasswordHash(int $userId, string $plainPassword, string $currentHash, string $table = PATIENTS_TABLE): void
{
    if (!in_array($table, [PATIENTS_TABLE, ADMINS_TABLE], true) || !password_needs_rehash($currentHash, PASSWORD_DEFAULT)) {
        return;
    }

    try {
        $statement = getDatabaseConnection()->prepare("UPDATE {$table} SET password_hash = :password_hash WHERE id = :id");
        $statement->execute(['password_hash' => hashPassword($plainPassword), 'id' => $userId]);
    } catch (PDOException $exception) {
        error_log('Password rehash failed for ' . $table . '#' . $userId . ': ' . $exception->getMessage());
    }
}
