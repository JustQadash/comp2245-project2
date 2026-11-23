<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_login();
$ticketId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$ticketId || $ticketId <= 0) {
    http_response_code(400);
    exit('Invalid ticket ID.');
}

// Retrieve the ticket.
$stmt = $pdo->prepare(
    "SELECT * FROM tickets WHERE id = ?"
);
$stmt->execute([$ticketId]);

$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    exit('Ticket not found.');
}

// Retrieve staff for the assignment dropdown.
$users = $pdo->query(
    "SELECT id, firstname, lastname
     FROM users
     ORDER BY firstname, lastname"
)->fetchAll();

$title = $ticket['title'];
$description = $ticket['description'];
$priority = $ticket['priority'];
$assignedTo = $ticket['assigned_to'] === null
    ? ''
    : (string) $ticket['assigned_to'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf($_POST['csrf_token'] ?? null);

    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim(
        (string) ($_POST['description'] ?? '')
    );

    $priority = $_POST['priority'] ?? '';
    $assignedTo = $_POST['assigned_to'] ?? '';

    // Validate title.
    if ($title === '') {
        $errors['title'] = 'Title is required.';
    } elseif ((function_exists('mb_strlen') ? mb_strlen($title) : strlen($title)) > 150) {
        $errors['title'] =
            'Title cannot exceed 150 characters.';
    }

    // Validate description.
    if ($description === '') {
        $errors['description'] =
            'Description is required.';
    }

    // Validate priority.
    if (!is_string($priority) ||
        !in_array(
            $priority,
            ['Low', 'Medium', 'High'],
            true
        )) {

        $errors['priority'] =
            'Choose a valid priority.';
    }

    // Validate assignee.
    $validAssignee = null;

    if (!is_string($assignedTo)) {
        $errors['assigned_to'] =
            'Choose a valid staff member.';

    } elseif ($assignedTo !== '') {

        $validAssignee = filter_var(
            $assignedTo,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($validAssignee === false) {
            $errors['assigned_to'] =
                'Choose a valid staff member.';
        } else {
            $check = $pdo->prepare(
                "SELECT id FROM users WHERE id = ?"
            );
            $check->execute([$validAssignee]);

            if (!$check->fetch()) {
                $errors['assigned_to'] =
                    'That staff member does not exist.';
            }
        }
    }

    // Save only when all inputs are valid.
    if (!$errors) {

        $stmt = $pdo->prepare("
            UPDATE tickets
            SET title = :title,
                description = :description,
                priority = :priority,
                assigned_to = :assigned_to
            WHERE id = :id
        ");

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'assigned_to' => $validAssignee,
            'id' => $ticketId
        ]);

        header(
            'Location: ticket.php?id=' . $ticketId
        );
        exit;
    }
}

$pageTitle = 'Edit Ticket';
require __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Edit Ticket #<?= (int) $ticketId ?></h1>

<div class="card form-narrow"><form method="POST"
      action="ticket-edit.php?id=<?= (int) $ticketId ?>">

    <?= csrf_field() ?>

    <div class="form-group">
        <label for="title">Title</label>

        <input
            type="text"
            id="title"
            name="title"
            maxlength="150"
            value="<?= e($title) ?>"
            required
        >

        <?php if (isset($errors['title'])): ?>
            <p class="error">
                <?= e($errors['title']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="description">Description</label>

        <textarea
            id="description"
            name="description"
            rows="6"
            required
        ><?= e($description) ?></textarea>

        <?php if (isset($errors['description'])): ?>
            <p class="error">
                <?= e($errors['description']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="priority">Priority</label>

        <select id="priority" name="priority" required>

            <?php foreach (
                ['Low', 'Medium', 'High'] as $option
            ): ?>

                <option
                    value="<?= e($option) ?>"
                    <?= $priority === $option
                        ? 'selected'
                        : '' ?>
                >
                    <?= e($option) ?>
                </option>

            <?php endforeach; ?>
        </select>

        <?php if (isset($errors['priority'])): ?>
            <p class="error">
                <?= e($errors['priority']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="assigned_to">Assign to</label>

        <select id="assigned_to" name="assigned_to">

            <option value="">
                Nobody
            </option>

            <?php foreach ($users as $staff): ?>

                <option
                    value="<?= (int) $staff['id'] ?>"
                    <?= (string) $assignedTo ===
                        (string) $staff['id']
                        ? 'selected'
                        : '' ?>
                >
                    <?= e(
                        $staff['firstname'] . ' ' .
                        $staff['lastname']
                    ) ?>
                </option>

            <?php endforeach; ?>
        </select>

        <?php if (isset($errors['assigned_to'])): ?>
            <p class="error">
                <?= e($errors['assigned_to']) ?>
            </p>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">
        Save Changes
    </button>

    <a class="btn btn-secondary" href="ticket.php?id=<?= (int) $ticketId ?>">
        Cancel
    </a>
</form></div>

<?php require __DIR__ . '/includes/footer.php'; ?>
