<?php
declare(strict_types=1);

// Start the session securely.
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/'
        ]);
    }
    session_start();
}

// Load database settings.
$configFile = __DIR__ . '/../config.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Database configuration is missing.');
}

require_once $configFile;

// Connect to MySQL using PDO.
try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to connect to the database.');
}

// Create one CSRF token per session.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}

// Escape output safely.
function e($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Get the current logged-in user.
function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;

    if (!is_array($user) || empty($user['id'])) {
        return null;
    }

    return $user;
}

// Redirect guests to login.
function require_login(): array
{
    $user = current_user();

    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

// Restrict a page to administrators.
function require_admin(): array
{
    $user = require_login();

    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('403 Forbidden: Administrators only.');
    }

    return $user;
}

// Return a hidden CSRF form field.
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        e($_SESSION['csrf_token']) . '">';
}

// Validate a submitted CSRF token.
function verify_csrf($submitted): void
{
    $expected = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submitted) ||
        !is_string($expected) ||
        $expected === '' ||
        !hash_equals($expected, $submitted)) {

        http_response_code(403);
        exit('403 Forbidden: Invalid CSRF token.');
    }
}
