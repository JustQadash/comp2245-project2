<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Already logged-in users go to the ticket list.
if (current_user() !== null) {
    header('Location: /index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf($_POST['csrf_token'] ?? null);

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Use one generic error message for failed logins.
    $error = "That email and password don't match an account.";

    if (is_string($password) && $email !== '') {

        $stmt = $pdo->prepare("
            SELECT
                id,
                firstname,
                lastname,
                email,
                password,
                role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();

        if (
            $user &&
            password_verify($password, $user['password'])
        ) {

            // Prevent session fixation.
            session_regenerate_id(true);

            // Store only necessary user information.
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            header('Location: /index.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
          content="<?= e($_SESSION['csrf_token']) ?>">

    <title>Log in — Fix-It Desk</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <div class="auth-container">

            <div class="card">

                <h1 class="auth-heading">
                    Fix-It Desk
                </h1>

                <p class="auth-subtitle">
                    Log in to see and update help desk tickets.
                </p>

                <?php if ($error !== ''): ?>

                    <div class="alert alert-error" role="alert">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>

                <form method="POST"
                      action="/login.php"
                      novalidate>

                    <?= csrf_field() ?>

                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="<?= e($email) ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                        >

                    </div>

                    <div class="form-actions">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Log in
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </main>

    <script src="/app.js" defer></script>

</body>
</html>
