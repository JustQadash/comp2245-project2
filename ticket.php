<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="">
        <title>#2 Cannot connect to staff Wi-Fi — Fix-It Desk</title>
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
                        <a class="nav-link" href="/user.php">Users</a>
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
            <a href="/index.php" class="back-link">&larr; All tickets</a>

            <div class="page-header">
                <h1 class="page-title">#2 Cannot connect to staff Wi-Fi</h1>
                <div class="header-actions">
                    <a href="/ticket-edit.php?id=2" class="btn btn-secondary">Edit</a>
                    <button type="button" id="delete-trigger-btn" class="btn btn-danger">Delete</button>
                </div>
            </div>

            <div id="delete-confirm-box" class="confirm-box" hidden>
                <p class="field-error" style="margin-top: 0;">Are you sure you want to delete this ticket and all of its notes?</p>
                <form method="POST" action="/ticket-delete.php" class="inline-form">
                    <input type="hidden" name="csrf_token" value="">
                    <input type="hidden" name="id" id="delete-ticket-id" value="2">
                    <button type="submit" class="btn btn-danger">Yes, delete ticket</button>
                    <button type="button" id="cancel-delete-btn" class="btn btn-secondary">Cancel</button>
                </form>
            </div>

            <div class="ticket-layout" data-ticket-id="2">
                <div>
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <p class="note-body">My laptop sees the StaffNet network but says "Can't connect". It worked last week.</p>
                    </div>

                    <div class="card">
                        <h2 style="margin: 0 0 1rem; font-size: 1.2rem;">Notes</h2>

                        <div id="notes-list" class="notes-list">
                            <article class="note-item">
                                <div class="note-meta">
                                    <strong>Reanna Christopher</strong> &middot; 27 Oct 2026, 3:02 pm
                                </div>
                                <p class="note-body">Forgot the network and joined again. Same error. Trying with a cable next.</p>
                            </article>
                            <article class="note-item">
                                <div class="note-meta">
                                    <strong>Reanna Christopher</strong> &middot; 28 Oct 2026, 9:40 am
                                </div>
                                <p class="note-body">The cable works, so it's the Wi-Fi profile. Will reset the certificate tomorrow.</p>
                            </article>
                            <article class="note-item">
                                <div class="note-meta">
                                    <strong>Admin User</strong> &middot; 31 Oct 2026, 9:52 pm
                                </div>
                                <p class="note-body">Reset the Wi-Fi certificate. Reanna says it connects now.</p>
                            </article>
                        </div>

                        <form id="add-note-form" method="POST" action="/api/notes.php">
                            <div class="form-group">
                                <label for="note-body">Add a note</label>
                                <textarea id="note-body" name="body" required></textarea>
                                <p id="note-error" class="field-error" hidden></p>
                            </div>
                            <button type="submit" class="btn btn-primary">Add note</button>
                        </form>
                    </div>
                </div>

                <aside class="card">
                    <dl class="meta-list">
                        <div class="meta-row">
                            <dt>Status</dt>
                            <dd><span id="ticket-status-badge" class="badge badge-status-closed">Closed</span></dd>
                        </div>
                        <div class="meta-row">
                            <dt>Priority</dt>
                            <dd><span class="badge badge-priority-high">High</span></dd>
                        </div>
                        <div class="meta-row">
                            <dt>Assigned to</dt>
                            <dd id="ticket-assignee">Reanna Christopher</dd>
                        </div>
                        <div class="meta-row">
                            <dt>Created by</dt>
                            <dd>Qadash Charles</dd>
                        </div>
                        <div class="meta-row">
                            <dt>Created</dt>
                            <dd>27 Oct 2026, 2:30 pm</dd>
                        </div>
                        <div class="meta-row">
                            <dt>Last updated</dt>
                            <dd id="ticket-updated-at">31 Oct 2026, 9:52 pm</dd>
                        </div>
                    </dl>

                    <div class="sidebar-action">
                        <label for="status-select">Change status</label>
                        <select id="status-select" name="status">
                            <option value="Open">Open</option>
                            <option value="In progress">In progress</option>
                            <option value="Closed" selected>Closed</option>
                        </select>
                    </div>

                    <div class="sidebar-action">
                        <button type="button" id="assign-to-me-btn" class="btn btn-secondary" style="width: 100%;">Assign to me</button>
                    </div>

                    <div id="sidebar-feedback" class="status-feedback" role="status" aria-live="polite"></div>
                </aside>
            </div>
        </main>

        <script src="/app.js" defer></script>
    </body>
</html>