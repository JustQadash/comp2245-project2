<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="">
        <title>Tickets — Fix-It Desk</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="site-header">
            <div class="navbar">
                <div class="nav-left">
                    <a class="brand" href="/index.php">Fix-It Desk</a>
                    <nav class="nav-links" aria-label="Main navigation">
                        <a class="nav-link is-active" href="/index.php">Tickets</a>
                        <a class="nav-link" href="/ticket-new.php">New Ticket</a>
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
            <div class="alert alert-success" role="status" hidden>Ticket deleted.</div>

            <div class="page-header">
                <h1 class="page-title">Tickets</h1>
                <a href="/ticket-new.php" class="btn btn-primary">New ticket</a>
            </div>

            <div class="filter-bar" role="group" aria-label="Filter tickets">
                <button type="button" class="filter-btn is-active" data-filter="all" aria-pressed="true">All</button>
                <button type="button" class="filter-btn" data-filter="open" aria-pressed="false">Open</button>
                <button type="button" class="filter-btn" data-filter="mine" aria-pressed="false">My tickets</button>
            </div>

            <p id="tickets-count" class="results-count">5 tickets</p>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Title</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created by</th>
                            <th scope="col">Assigned to</th>
                            <th scope="col">Created</th>
                        </tr>
                    </thead>
                    <tbody id="tickets-tbody">
                        <tr>
                            <td>4</td>
                            <td><a href="/ticket.php?id=4">Email bounces from the finance office</a></td>
                            <td><span class="badge badge-priority-high">High</span></td>
                            <td><span class="badge badge-status-open">Open</span></td>
                            <td>Admin User</td>
                            <td>Admin User</td>
                            <td>29 Oct 2026, 11:47 am</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><a href="/ticket.php?id=3">Projector remote missing in Lab B</a></td>
                            <td><span class="badge badge-priority-low">Low</span></td>
                            <td><span class="badge badge-status-open">Open</span></td>
                            <td>Varyl Browne</td>
                            <td><span class="muted">Nobody</span></td>
                            <td>28 Oct 2026, 8:05 am</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><a href="/ticket.php?id=2">Cannot connect to staff Wi-Fi</a></td>
                            <td><span class="badge badge-priority-high">High</span></td>
                            <td><span class="badge badge-status-in-progress">In progress</span></td>
                            <td>Qadash Charles</td>
                            <td>Reanna Christopher</td>
                            <td>27 Oct 2026, 2:30 pm</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td><a href="/ticket.php?id=1">Printer in Room 4 jams on every page</a></td>
                            <td><span class="badge badge-priority-medium">Medium</span></td>
                            <td><span class="badge badge-status-open">Open</span></td>
                            <td>Reanna Christopher</td>
                            <td>Varyl Browne</td>
                            <td>26 Oct 2026, 9:12 am</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><a href="/ticket.php?id=5">New starter needs an account</a></td>
                            <td><span class="badge badge-priority-medium">Medium</span></td>
                            <td><span class="badge badge-status-closed">Closed</span></td>
                            <td>Admin User</td>
                            <td>Reanna Christopher</td>
                            <td>21 Oct 2026, 7:18 pm</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <script src="/app.js" defer></script>
    </body>
</html>