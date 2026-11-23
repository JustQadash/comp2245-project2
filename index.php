<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$pageTitle = 'Tickets';
$activePage = 'tickets';
require __DIR__ . '/includes/header.php';
?>
<?php if (($_GET['deleted'] ?? '') === '1'): ?>
<div class="alert alert-success" role="status">Ticket deleted.</div>
<?php endif; ?>
<div class="page-header">
<h1 class="page-title">Tickets</h1>
<a href="/ticket-new.php" class="btn btn-primary">New ticket</a>
</div>
<div class="filter-bar" role="group" aria-label="Filter tickets">
<button type="button" class="filter-btn is-active" data-ticket-filter="all" aria-pressed="true">All</button>
<button type="button" class="filter-btn" data-ticket-filter="open" aria-pressed="false">Open</button>
<button type="button" class="filter-btn" data-ticket-filter="mine" aria-pressed="false">My tickets</button>
</div>
<p id="ticket-count" class="results-count">Loading tickets...</p>
<p id="tickets-message" class="field-error" role="alert" hidden></p>
<div class="table-container">
<table class="data-table">
<thead><tr><th>#</th><th>Title</th><th>Priority</th><th>Status</th><th>Created by</th><th>Assigned to</th><th>Created</th></tr></thead>
<tbody id="tickets-body"><tr><td colspan="7">Loading...</td></tr></tbody>
</table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
