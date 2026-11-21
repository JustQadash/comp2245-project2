<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="">
        <title>Log in — Fix-It Desk</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <main class="container">
            <div class="auth-container">
                <div class="card">
                    <h1 class="auth-heading">Fix-It Desk</h1>
                    <p class="auth-subtitle">Log in to see and update help desk tickets.</p>

                    <div class="alert alert-error" role="alert" hidden>
                        That email and password don't match an account.
                    </div>

                    <form method="POST" action="/login.php" novalidate>
                        <input type="hidden" name="csrf_token" value="">

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Log in</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <script src="/app.js" defer></script>
    </body>
</html>