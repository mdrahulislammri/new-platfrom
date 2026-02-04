<?php
declare(strict_types=1);

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function require_user_login(): void
{
    start_session();
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_admin_login(): void
{
    start_session();
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function generate_license(string $email, string $moduleName, string $domain, string $secret): string
{
    $seed = implode('|', [$email, $moduleName, $domain, $secret, bin2hex(random_bytes(16))]);
    $hash = strtoupper(hash('sha256', $seed));
    $raw = substr($hash, 0, 16);

    return sprintf(
        'STXV-%s-%s-%s-%s',
        substr($raw, 0, 4),
        substr($raw, 4, 4),
        substr($raw, 8, 4),
        substr($raw, 12, 4)
    );
}

function is_valid_license_format(string $license): bool
{
    return (bool) preg_match('/^STXV-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}$/', $license);
}

function sanitize_string(string $value): string
{
    return trim(filter_var($value, FILTER_SANITIZE_STRING));
}

function flash_message(string $key, ?string $message = null): ?string
{
    start_session();
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}
