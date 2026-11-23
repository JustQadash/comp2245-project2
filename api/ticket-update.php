<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

require_ajax_method('POST');
$userId = require_ajax_login();

$data = read_json_body();
check_ajax_csrf($data);

$ticketId = positive_id($data['ticket_id'] ?? null);
$action = $data['action'] ?? '';

if (!is_string($action) ||
    !in_array($action, ['status', 'assign_to_me'], true)) {
    json_response([
        'success' => false,
        'error' => 'Invalid action.'
    ], 400);
}

try {
    // Confirm that this ticket exists.
    $stmt = $pdo->prepare(
        "SELECT id FROM tickets WHERE id = ?"
    );
    $stmt->execute([$ticketId]);

    if (!$stmt->fetch()) {
        json_response([
            'success' => false,
            'error' => 'Ticket not found.'
        ], 404);
    }

    if ($action === 'status') {
        $status = $data['status'] ?? '';

        $allowedStatuses = [
            'Open',
            'In progress',
            'Closed'
        ];

        if (!is_string($status) ||
            !in_array($status, $allowedStatuses, true)) {
            json_response([
                'success' => false,
                'error' => 'Invalid ticket status.'
            ], 422);
        }

        $stmt = $pdo->prepare("
            UPDATE tickets
            SET status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute([
            'status' => $status,
            'id' => $ticketId
        ]);
    }

    if ($action === 'assign_to_me') {
        $stmt = $pdo->prepare("
            UPDATE tickets
            SET assigned_to = :user_id,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute([
            'user_id' => $userId,
            'id' => $ticketId
        ]);
    }

    // Return the saved ticket state.
    $stmt = $pdo->prepare("
        SELECT
            t.id,
            t.status,
            t.assigned_to AS assigned_to_id,
            t.updated_at,
            CONCAT(u.firstname, ' ', u.lastname)
                AS assigned_to_name
        FROM tickets AS t
        LEFT JOIN users AS u
            ON t.assigned_to = u.id
        WHERE t.id = :id
    ");

    $stmt->execute(['id' => $ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'message' => 'Ticket updated successfully.',
        'ticket' => $ticket
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    json_response([
        'success' => false,
        'error' => 'Unable to update ticket.'
    ], 500);
}
