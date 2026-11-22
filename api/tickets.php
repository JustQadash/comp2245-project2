<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

require_ajax_method('GET');
$userId = require_ajax_login();

$filter = $_GET['filter'] ?? 'all';

if (!in_array($filter, ['all', 'open', 'mine'], true)) {
    json_response([
        'success' => false,
        'error' => 'Invalid ticket filter.'
    ], 400);
}

try {
    $sql = "
        SELECT
            t.id,
            t.title,
            t.priority,
            t.status,
            t.created_at,
            CONCAT(c.firstname, ' ', c.lastname)
                AS created_by_name,
            CONCAT(a.firstname, ' ', a.lastname)
                AS assigned_to_name
        FROM tickets AS t
        INNER JOIN users AS c
            ON t.created_by = c.id
        LEFT JOIN users AS a
            ON t.assigned_to = a.id
    ";

    $params = [];

    if ($filter === 'open') {
        $sql .= " WHERE t.status <> :status";
        $params['status'] = 'Closed';
    }

    if ($filter === 'mine') {
        $sql .= " WHERE t.assigned_to = :user_id";
        $params['user_id'] = $userId;
    }

    $sql .= " ORDER BY t.created_at DESC, t.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'success' => true,
        'tickets' => $tickets,
        'count' => count($tickets)
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    json_response([
        'success' => false,
        'error' => 'Unable to load tickets.'
    ], 500);
}
