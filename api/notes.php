<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

require_ajax_method('POST');
$userId = require_ajax_login();

$data = read_json_body();
check_ajax_csrf($data);

$ticketId = positive_id($data['ticket_id'] ?? null);
$body = $data['body'] ?? '';

if (!is_string($body)) {
    json_response([
        'success' => false,
        'error' => 'Invalid note.'
    ], 400);
}

$body = trim($body);

if ($body === '') {
    json_response([
        'success' => false,
        'error' => 'Please enter a note.'
    ], 422);
}

if (strlen($body) > 60000) {
    json_response([
        'success' => false,
        'error' => 'The note is too long.'
    ], 422);
}

try {
    // Verify that the ticket exists.
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

    // Save note using a prepared statement.
    $stmt = $pdo->prepare("
        INSERT INTO notes
            (ticket_id, body, created_by)
        VALUES
            (:ticket_id, :body, :created_by)
    ");

    $stmt->execute([
        'ticket_id' => $ticketId,
        'body' => $body,
        'created_by' => $userId
    ]);

    $noteId = (int) $pdo->lastInsertId();

    // Retrieve the saved note and author's name.
    $stmt = $pdo->prepare("
        SELECT
            n.id,
            n.body,
            n.created_at,
            CONCAT(u.firstname, ' ', u.lastname)
                AS author_name
        FROM notes AS n
        INNER JOIN users AS u
            ON n.created_by = u.id
        WHERE n.id = :note_id
    ");

    $stmt->execute(['note_id' => $noteId]);

    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'message' => 'Note added successfully.',
        'note' => $note
    ], 201);

} catch (PDOException $e) {
    error_log($e->getMessage());

    json_response([
        'success' => false,
        'error' => 'Unable to save note.'
    ], 500);
}
