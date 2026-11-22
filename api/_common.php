<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// Shared bootstrap.php must start the session and create $pdo.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_ajax_login(): int
{
    $user = $_SESSION['user'] ?? null;

    if (!is_array($user) || empty($user['id'])) {
        json_response([
            'success' => false,
            'error' => 'You must log in first.'
        ], 401);
    }

    return (int) $user['id'];
}

function require_ajax_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        json_response([
            'success' => false,
            'error' => 'Method not allowed.'
        ], 405);
    }
}

function read_json_body(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!is_array($data)) {
        json_response([
            'success' => false,
            'error' => 'Invalid JSON request.'
        ], 400);
    }

    return $data;
}

function check_ajax_csrf(array $data): void
{
    $expected = $_SESSION['csrf_token'] ?? '';

    $received = $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? ($data['csrf_token'] ?? '');

    if (!is_string($received) ||
        !is_string($expected) ||
        $expected === '' ||
        !hash_equals($expected, $received)) {

        json_response([
            'success' => false,
            'error' => 'Invalid CSRF token.'
        ], 403);
    }
}

function positive_id($value): int
{
    $id = filter_var(
        $value,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        json_response([
            'success' => false,
            'error' => 'Invalid ticket ID.'
        ], 400);
    }

    return (int) $id;
}
