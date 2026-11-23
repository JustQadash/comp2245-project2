<?php
require_once __DIR__ . '/bootstrap.php';

$loggedInUser = current_user();

$pageTitle = $pageTitle ?? 'Fix-It Desk';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="<?= e($_SESSION['csrf_token']) ?>">

    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<header class="site-header">
    <nav class="navbar">

        <a href="index.php" class="logo">
            Fix-It Desk
        </a>

        <?php if ($loggedInUser): ?>

            <a href="index.php">Tickets</a>

            <a href="ticket-new.php">New ticket</a>

            <?php if (
                ($loggedInUser['role'] ?? '') === 'admin'
            ): ?>
                <a href="user.php">Users</a>
            <?php endif; ?>

            <span class="current-user">
                <?= e(
                    trim(
                        ($loggedInUser['firstname'] ?? '') .
                        ' ' .
                        ($loggedInUser['lastname'] ?? '')
                    )
                ) ?>
            </span>

            <form method="POST" action="logout.php">
                <?= csrf_field() ?>

                <button type="submit">
                    Log out
                </button>
            </form>

        <?php endif; ?>

    </nav>
</header>

<main class="container">
