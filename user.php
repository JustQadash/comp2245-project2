<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();
$first = ''; $last = ''; $email = ''; $role = 'member'; $errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);
    $first = trim((string) ($_POST['firstname'] ?? ''));
    $last = trim((string) ($_POST['lastname'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    if ($first === '' || (function_exists('mb_strlen') ? mb_strlen($first) : strlen($first)) > 50) $errors['firstname'] = 'Enter a first name of up to 50 characters.';
    if ($last === '' || (function_exists('mb_strlen') ? mb_strlen($last) : strlen($last)) > 50) $errors['lastname'] = 'Enter a last name of up to 50 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) $errors['email'] = 'Enter a valid email address.';
    if (!is_string($password) || strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = 'Password must have at least 8 characters, a letter and a number.';
    if (!is_string($role) || !in_array($role, ['admin','member'], true)) $errors['role'] = 'Choose a valid role.';
    if (!isset($errors['email'])) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors['email'] = 'Email already registered.';
    }
    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$first, $last, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $success = 'User added successfully.';
            $first = $last = $email = ''; $role = 'member';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') $errors['email'] = 'Email already registered.';
            else { error_log($e->getMessage()); $errors['general'] = 'Unable to add user.'; }
        }
    }
}
$staff = $pdo->query('SELECT firstname, lastname, email, role, created_at FROM users ORDER BY created_at DESC, id DESC')->fetchAll();
$pageTitle = 'Users'; $activePage = 'users';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header"><h1 class="page-title">Users</h1></div>
<?php if ($success): ?><div class="alert alert-success" role="status"><?= e($success) ?></div><?php endif; ?>
<?php if (isset($errors['general'])): ?><div class="alert alert-error"><?= e($errors['general']) ?></div><?php endif; ?>
<div class="users-layout"><div class="table-container"><table class="data-table">
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Added</th></tr></thead><tbody>
<?php foreach ($staff as $person): ?><tr>
<td><?= e($person['firstname'] . ' ' . $person['lastname']) ?></td>
<td><?= e($person['email']) ?></td><td><?= e(ucfirst($person['role'])) ?></td><td><?= e($person['created_at']) ?></td>
</tr><?php endforeach; ?>
</tbody></table></div>
<div class="card"><h2 style="margin:0 0 1rem;font-size:1.2rem">Add a user</h2>
<form method="POST" action="/user.php" novalidate>
<?= csrf_field() ?>
<div class="form-row">
<div class="form-group"><label for="firstname">First name</label><input id="firstname" name="firstname" type="text" maxlength="50" required value="<?= e($first) ?>"><?php if (isset($errors['firstname'])): ?><p class="field-error"><?= e($errors['firstname']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="lastname">Last name</label><input id="lastname" name="lastname" type="text" maxlength="50" required value="<?= e($last) ?>"><?php if (isset($errors['lastname'])): ?><p class="field-error"><?= e($errors['lastname']) ?></p><?php endif; ?></div>
</div>
<div class="form-group"><label for="user-email">Email</label><input id="user-email" name="email" type="email" required value="<?= e($email) ?>"><?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="user-password">Password</label><input id="user-password" name="password" type="password" required><p class="field-hint">At least 8 characters, with a letter and a number.</p><?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="role">Role</label><select id="role" name="role"><option value="member" <?= $role === 'member' ? 'selected' : '' ?>>Member</option><option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option></select><?php if (isset($errors['role'])): ?><p class="field-error"><?= e($errors['role']) ?></p><?php endif; ?></div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Add user</button></div>
</form></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
