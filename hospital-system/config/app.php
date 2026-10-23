<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

const SESSION_IDLE_TIMEOUT = 1800;
const SESSION_ABSOLUTE_TIMEOUT = 7200;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function requestIsHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function clientIpAddress(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function clientFingerprint(): string
{
    return hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('HMSSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => requestIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.gc_maxlifetime', (string) SESSION_IDLE_TIMEOUT);
    session_start();
}

function destroySession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parameters = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $parameters['path'],
            'domain' => $parameters['domain'],
            'secure' => $parameters['secure'],
            'httponly' => $parameters['httponly'],
            'samesite' => $parameters['samesite'] ?? 'Lax',
        ]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function logoutCurrentSession(string $redirectTo): never
{
    destroySession();
    redirect($redirectTo);
}

function sessionLoginPath(): string
{
    $directory = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.html'))), '/');

    return $directory . '/login.php';
}

function sessionTerminationReason(): ?string
{
    if (empty($_SESSION['patient_id']) && empty($_SESSION['admin_id'])) {
        return null;
    }

    if (($_SESSION['fingerprint'] ?? null) !== clientFingerprint()) {
        return 'untrusted';
    }

    $now = time();

    if ($now > (int) ($_SESSION['expires_at'] ?? 0)) {
        return 'expired';
    }

    if ($now - (int) ($_SESSION['last_activity'] ?? 0) > SESSION_IDLE_TIMEOUT) {
        return 'idle';
    }

    $_SESSION['last_activity'] = $now;

    return null;
}

function enforceSessionTracking(): void
{
    $reason = sessionTerminationReason();

    if ($reason !== null) {
        destroySession();
        redirect(sessionLoginPath() . '?session=' . $reason);
    }
}

function beginAuthenticatedSession(string $idKey, int $userId, array $attributes = []): void
{
    session_regenerate_id(true);

    $now = time();
    $_SESSION = [];
    $_SESSION[$idKey] = $userId;
    $_SESSION['login_time'] = $now;
    $_SESSION['last_activity'] = $now;
    $_SESSION['expires_at'] = $now + SESSION_ABSOLUTE_TIMEOUT;
    $_SESSION['ip_address'] = clientIpAddress();
    $_SESSION['fingerprint'] = clientFingerprint();

    foreach ($attributes as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

function currentSessionSummary(): ?array
{
    $isAdmin = !empty($_SESSION['admin_id']);
    $isPatient = !empty($_SESSION['patient_id']);

    if (!$isAdmin && !$isPatient) {
        return null;
    }

    return [
        'identity' => (string) ($_SESSION['patient_name'] ?? $_SESSION['patient_email'] ?? $_SESSION['admin_email'] ?? 'Unknown'),
        'role' => $isAdmin ? 'Administrator' : 'Patient',
        'started_at' => (int) ($_SESSION['login_time'] ?? time()),
        'last_activity' => (int) ($_SESSION['last_activity'] ?? time()),
        'expires_at' => (int) ($_SESSION['expires_at'] ?? time() + SESSION_ABSOLUTE_TIMEOUT),
        'idle_timeout' => SESSION_IDLE_TIMEOUT,
        'ip_address' => (string) ($_SESSION['ip_address'] ?? 'unknown'),
    ];
}

function sessionStatusHtml(): string
{
    $summary = currentSessionSummary();

    if ($summary === null) {
        return '';
    }

    return '<div class="alert alert-info">Session: signed in as <strong>' . e($summary['identity']) . '</strong> (' . e($summary['role']) . ')'
        . ' &middot; started ' . e(date('H:i', $summary['started_at']))
        . ' &middot; last activity ' . e(date('H:i', $summary['last_activity']))
        . ' &middot; signs off after ' . intdiv($summary['idle_timeout'], 60) . ' min idle'
        . ' &middot; expires ' . e(date('H:i', $summary['expires_at']))
        . ' &middot; IP ' . e($summary['ip_address']) . '</div>';
}

function sessionNoticeMessage(?string $reason): ?string
{
    return match ($reason) {
        'idle' => 'You were signed out after ' . intdiv(SESSION_IDLE_TIMEOUT, 60) . ' minutes of inactivity.',
        'expired' => 'Your session reached its maximum length and was closed. Please log in again.',
        'untrusted' => 'Your session was closed because it was used from a different browser.',
        default => null,
    };
}

function requireLogin(): void
{
    if (empty($_SESSION['patient_id'])) {
        redirect(sessionLoginPath());
    }
}

function currentPatientId(): int
{
    return (int) $_SESSION['patient_id'];
}

startSecureSession();
enforceSessionTracking();
