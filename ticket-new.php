<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$staff = $pdo->query('SELECT id, firstname, lastname FROM users ORDER BY firstname, lastname')->fetchAll();
$title = ''; $description = ''; $priority = ''; $assignedTo = ''; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $priority = $_POST['priority'] ?? '';
    $assignedTo = $_POST['assigned_to'] ?? '';
    if ($title === '') $errors['title'] = 'Enter a title.';
    elseif ((function_exists('mb_strlen') ? mb_strlen($title) : strlen($title)) > 150) $errors['title'] = 'Title must not exceed 150 characters.';
    if ($description === '') $errors['description'] = 'Describe the problem.';
    if (!is_string($priority) || !in_array($priority, ['Low','Medium','High'], true)) $errors['priority'] = 'Choose a priority.';
    $assigneeId = null;
    if (!is_string($assignedTo)) $errors['assigned_to'] = 'Choose a valid staff member.';
    elseif ($assignedTo !== '') {
        $assigneeId = filter_var($assignedTo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($assigneeId === false) $errors['assigned_to'] = 'Choose a valid staff member.';
        else {
            $check = $pdo->prepare('SELECT id FROM users WHERE id = ?');
            $check->execute([$assigneeId]);
            if (!$check->fetch()) $errors['assigned_to'] = 'That staff member does not exist.';
        }
    }
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO tickets (title, description, priority, created_by, assigned_to) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $priority, (int) $user['id'], $assigneeId]);
        header('Location: /ticket.php?id=' . (int) $pdo->lastInsertId());
        exit;
    }
}
$pageTitle = 'New Ticket'; $activePage = 'new';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1 class="page-title">New ticket</h1></div>
<div class="card form-narrow">
<form method="POST" action="/ticket-new.php" novalidate>
<?= csrf_field() ?>
<div class="form-group">
<label for="title">Title</label>
<input id="title" name="title" type="text" maxlength="150" value="<?= e($title) ?>" required>
<?php if (isset($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
</div>
<div class="form-group">
<label for="description">Description</label>
<textarea id="description" name="description" required><?= e($description) ?></textarea>
<?php if (isset($errors['description'])): ?><p class="field-error"><?= e($errors['description']) ?></p><?php endif; ?>
</div>
<div class="form-row"><div class="form-group">
<label for="priority">Priority</label><select id="priority" name="priority" required>
<option value="">Choose...</option>
<?php foreach (['Low','Medium','High'] as $choice): ?><option value="<?= e($choice) ?>" <?= $priority === $choice ? 'selected' : '' ?>><?= e($choice) ?></option><?php endforeach; ?>
</select>
<?php if (isset($errors['priority'])): ?><p class="field-error"><?= e($errors['priority']) ?></p><?php endif; ?>
</div><div class="form-group">
<label for="assigned_to">Assign to</label><select id="assigned_to" name="assigned_to">
<option value="">Nobody yet</option>
<?php foreach ($staff as $person): ?><option value="<?= (int) $person['id'] ?>" <?= (string) $assignedTo === (string) $person['id'] ? 'selected' : '' ?>><?= e($person['firstname'] . ' ' . $person['lastname']) ?></option><?php endforeach; ?>
</select>
<?php if (isset($errors['assigned_to'])): ?><p class="field-error"><?= e($errors['assigned_to']) ?></p><?php endif; ?>
</div></div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Create ticket</button><a href="/index.php" class="btn btn-secondary">Cancel</a></div>
</form></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
