<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = require_login();

$ticketId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$ticketId || $ticketId <= 0) {
    http_response_code(400);
    exit('Invalid ticket ID.');
}

// Retrieve ticket information.
$stmt = $pdo->prepare("
    SELECT id, title, created_by
    FROM tickets
    WHERE id = ?
");

$stmt->execute([$ticketId]);

$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    exit('Ticket not found.');
}

// Only admin or creator can delete.
$isAdmin = ($user['role'] ?? '') === 'admin';

$isCreator =
    (int) $ticket['created_by'] ===
    (int) $user['id'];

if (!$isAdmin && !$isCreator) {
    http_response_code(403);
    exit('You are not allowed to delete this ticket.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf($_POST['csrf_token'] ?? null);

    // Recheck permission in the DELETE condition, without repeated placeholders.
    if ($isAdmin) {
        $stmt = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
        $stmt->execute([$ticketId]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM tickets WHERE id = ? AND created_by = ?');
        $stmt->execute([$ticketId, (int) $user['id']]);
    }

    if ($stmt->rowCount() !== 1) {
        http_response_code(403);
        exit('Ticket deletion was not permitted.');
    }

    header('Location: index.php?deleted=1');
    exit;
}

$pageTitle = 'Delete Ticket';

require __DIR__ . '/includes/header.php';
?>

<h1>Delete Ticket</h1>

<p>
    Are you sure you want to delete
    <strong><?= e($ticket['title']) ?></strong>?
</p>

<p>
    This action cannot be undone.
</p>

<form
    method="POST"
    action="ticket-delete.php?id=<?= (int) $ticketId ?>"
>
    <?= csrf_field() ?>

    <button type="submit">
        Yes, Delete Ticket
    </button>

    <a href="ticket.php?id=<?= (int) $ticketId ?>">
        Cancel
    </a>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
