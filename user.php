<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="">
        <title>Users — Fix-It Desk</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="site-header">
            <div class="navbar">
                <div class="nav-left">
                    <a class="brand" href="/index.php">Fix-It Desk</a>
                    <nav class="nav-links" aria-label="Main navigation">
                        <a class="nav-link" href="/index.php">Tickets</a>
                        <a class="nav-link" href="/ticket-new.php">New Ticket</a>
                        <a class="nav-link is-active" href="/users.php">Users</a>
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
                <h1 class="page-title">Users</h1>
            </div>

            <div class="users-layout">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Qadash Charles</td>
                                <td>qadash@fixit.test</td>
                                <td>Member</td>
                                <td>31 Oct 2026</td>
                            </tr>
                            <tr>
                                <td>Admin User</td>
                                <td>admin@fixit.test</td>
                                <td>Admin</td>
                                <td>31 Oct 2026</td>
                            </tr>
                            <tr>
                                <td>Reanna Christopher</td>
                                <td>reanna@fixit.test</td>
                                <td>Member</td>
                                <td>31 Oct 2026</td>
                            </tr>
                            <tr>
                                <td>Varyl Browne</td>
                                <td>varyl@fixit.test</td>
                                <td>Member</td>
                                <td>31 Oct 2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Add a user</h2>

                    <form method="POST" action="/users.php" novalidate>
                        <input type="hidden" name="csrf_token" value="">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstname">First name</label>
                                <input id="firstname" name="firstname" type="text">
                                <p class="field-error" hidden>Enter a first name.</p>
                            </div>

                            <div class="form-group">
                                <label for="lastname">Last name</label>
                                <input id="lastname" name="lastname" type="text">
                                <p class="field-error" hidden>Enter a last name.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="user-email">Email</label>
                            <input id="user-email" name="email" type="email">
                            <p class="field-error" hidden>Enter a valid email address.</p>
                        </div>

                        <div class="form-group">
                            <label for="user-password">Password</label>
                            <input id="user-password" name="password" type="password">
                            <p class="field-hint">At least 8 characters, with a letter and a number.</p>
                            <p class="field-error" hidden>Password must be at least 8 characters with a letter and a number.</p>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="member" selected>Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add user</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <script src="/app.js" defer></script>
    </body>
</html>