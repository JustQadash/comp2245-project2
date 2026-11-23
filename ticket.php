<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Require login before accessing tickets.
$user = require_login();

// Get the selected ticket ID.
$ticketId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$ticketId || $ticketId <= 0) {
    http_response_code(400);
    exit('Invalid ticket ID.');
}

// Retrieve ticket details and associated users.
$stmt = $pdo->prepare("
    SELECT
        t.*,
        CONCAT(
            creator.firstname,
            ' ',
            creator.lastname
        ) AS creator_name,
        CONCAT(
            assignee.firstname,
            ' ',
            assignee.lastname
        ) AS assignee_name
    FROM tickets AS t
    INNER JOIN users AS creator
        ON t.created_by = creator.id
    LEFT JOIN users AS assignee
        ON t.assigned_to = assignee.id
    WHERE t.id = :id
");

$stmt->execute(['id' => $ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    exit('Ticket not found.');
}

// Retrieve notes in chronological order.
$stmt = $pdo->prepare("
    SELECT
        n.id,
        n.body,
        n.created_at,
        CONCAT(
            u.firstname,
            ' ',
            u.lastname
        ) AS author_name
    FROM notes AS n
    INNER JOIN users AS u
        ON n.created_by = u.id
    WHERE n.ticket_id = :ticket_id
    ORDER BY n.created_at ASC, n.id ASC
");

$stmt->execute(['ticket_id' => $ticketId]);
$notes = $stmt->fetchAll();

// Determine user permissions.
$isAdmin = ($user['role'] ?? '') === 'admin';

$isCreator =
    (int) $ticket['created_by'] ===
    (int) $user['id'];

$canDelete = $isAdmin || $isCreator;

// Check if ticket is assigned to current user.
$isAssignedToMe =
    $ticket['assigned_to'] !== null &&
    (int) $ticket['assigned_to'] ===
    (int) $user['id'];

$assignedName = $ticket['assignee_name'] ?? 'Nobody';

// Format database timestamps.
function format_ticket_date($date): string
{
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);

    return $timestamp === false
        ? ''
        : date('d M Y, g:i a', $timestamp);
}

// Match the status classes in style.css.
$statusClass = match ($ticket['status']) {
    'Open' => 'badge-status-open',
    'In progress' => 'badge-status-in-progress',
    'Closed' => 'badge-status-closed',
    default => ''
};

// Match priority classes in style.css.
$priorityClass = match ($ticket['priority']) {
    'Low' => 'badge-priority-low',
    'Medium' => 'badge-priority-medium',
    'High' => 'badge-priority-high',
    default => ''
};
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="<?= e($_SESSION['csrf_token']) ?>"
    >

    <title>
        #<?= (int) $ticket['id'] ?>
        <?= e($ticket['title']) ?> — Fix-It Desk
    </title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="site-header">
    <div class="navbar">

        <div class="nav-left">

            <a class="brand" href="/index.php">
                Fix-It Desk
            </a>

            <nav
                class="nav-links"
                aria-label="Main navigation"
            >

                <a
                    class="nav-link is-active"
                    href="/index.php"
                >
                    Tickets
                </a>

                <a
                    class="nav-link"
                    href="/ticket-new.php"
                >
                    New Ticket
                </a>

                <?php if ($isAdmin): ?>
                    <a
                        class="nav-link"
                        href="/user.php"
                    >
                        Users
                    </a>
                <?php endif; ?>

            </nav>
        </div>

        <div class="nav-right">

            <span class="current-user">
                <?= e(
                    trim(
                        ($user['firstname'] ?? '') .
                        ' ' .
                        ($user['lastname'] ?? '')
                    )
                ) ?>
            </span>

            <form
                class="inline-form"
                method="POST"
                action="/logout.php"
            >

                <?= csrf_field() ?>

                <button
                    class="btn btn-link"
                    type="submit"
                >
                    Log out
                </button>

            </form>
        </div>
    </div>
</header>

<main class="container">

    <a
        href="/index.php"
        class="back-link"
    >
        &larr; All tickets
    </a>

    <div class="page-header">

        <h1 class="page-title">
            #<?= (int) $ticket['id'] ?>
            <?= e($ticket['title']) ?>
        </h1>

        <div class="header-actions">

            <a
                href="/ticket-edit.php?id=<?= (int) $ticket['id'] ?>"
                class="btn btn-secondary"
            >
                Edit
            </a>

            <?php if ($canDelete): ?>
                <button
                    type="button"
                    id="delete-trigger-btn"
                    class="btn btn-danger"
                >
                    Delete
                </button>
            <?php endif; ?>

        </div>
    </div>

    <!-- Confirm deletion before submitting -->

    <?php if ($canDelete): ?>

        <div
            id="delete-confirm-box"
            class="confirm-box"
            hidden
        >

            <p class="field-error" style="margin-top: 0;">
                Are you sure you want to delete
                this ticket and all of its notes?
            </p>

            <form
                method="POST"
                action="/ticket-delete.php?id=<?= (int) $ticket['id'] ?>"
                class="inline-form"
            >

                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $ticket['id'] ?>"
                >

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    Yes, delete ticket
                </button>

                <button
                    type="button"
                    id="cancel-delete-btn"
                    class="btn btn-secondary"
                >
                    Cancel
                </button>

            </form>
        </div>

    <?php endif; ?>

    <!-- Main ticket content -->

    <div
        id="ticket-page"
        class="ticket-layout"
        data-ticket-id="<?= (int) $ticket['id'] ?>"
    >

        <div>

            <!-- Ticket Description -->

            <div
                class="card"
                style="margin-bottom: 1.5rem;"
            >

                <p class="note-body">
                    <?= e($ticket['description']) ?>
                </p>

            </div>

            <!-- Ticket Notes -->

            <div class="card">

                <h2
                    style="margin: 0 0 1rem; font-size: 1.2rem;"
                >
                    Notes
                </h2>

                <ul
                    id="notes-list"
                    class="notes-list"
                    style="list-style: none; padding: 0;"
                >

                    <?php foreach ($notes as $note): ?>

                        <li class="note-item">

                            <div class="note-meta">

                                <strong>
                                    <?= e($note['author_name']) ?>
                                </strong>

                                &middot;

                                <?= e(
                                    format_ticket_date(
                                        $note['created_at']
                                    )
                                ) ?>

                            </div>

                            <p class="note-body">
                                <?= e($note['body']) ?>
                            </p>

                        </li>

                    <?php endforeach; ?>

                </ul>

                <!-- AJAX Add Note Form -->

                <form
                    id="note-form"
                    method="POST"
                    action="/api/notes.php"
                >

                    <?= csrf_field() ?>

                    <div class="form-group">

                        <label for="note-body">
                            Add a note
                        </label>

                        <textarea
                            id="note-body"
                            name="body"
                            required
                        ></textarea>

                        <p
                            id="note-message"
                            class="field-error"
                            hidden
                        ></p>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add note
                    </button>

                </form>

            </div>
        </div>

        <!-- Ticket Details Sidebar -->

        <aside class="card">

            <dl class="meta-list">

                <div class="meta-row">

                    <dt>Status</dt>

                    <dd>
                        <span
                            id="current-status"
                            class="badge <?= e($statusClass) ?>"
                        >
                            <?= e($ticket['status']) ?>
                        </span>
                    </dd>

                </div>

                <div class="meta-row">

                    <dt>Priority</dt>

                    <dd>
                        <span
                            class="badge <?= e($priorityClass) ?>"
                        >
                            <?= e($ticket['priority']) ?>
                        </span>
                    </dd>

                </div>

                <div class="meta-row">

                    <dt>Assigned to</dt>

                    <dd id="assigned-to-name">
                        <?= e($assignedName) ?>
                    </dd>

                </div>

                <div class="meta-row">

                    <dt>Created by</dt>

                    <dd>
                        <?= e($ticket['creator_name']) ?>
                    </dd>

                </div>

                <div class="meta-row">

                    <dt>Created</dt>

                    <dd>
                        <?= e(
                            format_ticket_date(
                                $ticket['created_at']
                            )
                        ) ?>
                    </dd>

                </div>

                <div class="meta-row">

                    <dt>Last updated</dt>

                    <dd id="last-updated">
                        <?= e(
                            format_ticket_date(
                                $ticket['updated_at']
                            )
                        ) ?>
                    </dd>

                </div>

            </dl>

            <!-- Change status with AJAX -->

            <div class="sidebar-action">

                <label for="ticket-status">
                    Change status
                </label>

                <select
                    id="ticket-status"
                    name="status"
                >

                    <?php foreach (
                        ['Open', 'In progress', 'Closed']
                        as $status
                    ): ?>

                        <option
                            value="<?= e($status) ?>"
                            <?= $ticket['status'] === $status
                                ? 'selected'
                                : '' ?>
                        >
                            <?= e($status) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <p
                    id="status-message"
                    class="status-feedback"
                    hidden
                ></p>

            </div>

            <!-- Assign to me with AJAX -->

            <?php if (!$isAssignedToMe): ?>

                <div class="sidebar-action">

                    <button
                        type="button"
                        id="assign-to-me"
                        class="btn btn-secondary"
                        style="width: 100%;"
                    >
                        Assign to me
                    </button>

                </div>

            <?php endif; ?>

            <div
                id="assign-message"
                class="status-feedback"
                role="status"
                aria-live="polite"
                hidden
            ></div>

        </aside>

    </div>

</main>

<script src="/app.js" defer></script>



</body>
</html>
