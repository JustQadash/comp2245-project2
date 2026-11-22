<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="">
        <title>New ticket — Fix-It Desk</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="site-header">
            <div class="navbar">
                <div class="nav-left">
                    <a class="brand" href="/index.php">Fix-It Desk</a>
                    <nav class="nav-links" aria-label="Main navigation">
                        <a class="nav-link" href="/index.php">Tickets</a>
                        <a class="nav-link is-active" href="/ticket-new.php">New Ticket</a>
                        <a class="nav-link" href="/users.php">Users</a>
                    </nav>
                </div>
                <div class="nav-right">
                    <span class="current-user">Admin User</span>
                    <form class="inline-form" method="POST" action="/logout.php">
                        <input type="hidden" name="csrf_token" value="">
                        <button class="btn btn-link" type="submit">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="container">
            <div class="page-header">
                <h1 class="page-title">New ticket</h1>
            </div>

            <div class="card form-narrow">
                <form method="POST" action="/ticket-new.php" novalidate>
                    <input type="hidden" name="csrf_token" value="">

                    <div class="form-group">
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" maxlength="150">
                        <p class="field-error" hidden>Enter a title.</p>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                        <p class="field-error" hidden>Describe the problem.</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority">
                                <option value="">Choose...</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                            <p class="field-error" hidden>Choose a priority.</p>
                        </div>

                        <div class="form-group">
                            <label for="assigned_to">Assign to</label>
                            <select id="assigned_to" name="assigned_to">
                                <option value="">Nobody yet</option>
                                <option value="1">Admin User</option>
                                <option value="2">Qadash Charles</option>
                                <option value="3">Reanna Christopher</option>
                                <option value="4">Varyl Browne</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create ticket</button>
                        <a href="/index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>

        <script src="/app.js" defer></script>
    </body>
</html>