<?php
$pageTitle = 'Dashboard';
ob_start();
?>
<h3 class="mb-4">Welcome, <?= htmlspecialchars($userName) ?> <span class="badge bg-secondary"><?= htmlspecialchars($roleName ?? '') ?></span></h3>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Flats</div>
                <div class="fs-3 fw-bold"><?= (int) $stats['flats'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Active Residents</div>
                <div class="fs-3 fw-bold"><?= (int) $stats['active_members'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Today's Visitors</div>
                <div class="fs-3 fw-bold"><?= (int) $stats['visitors_today'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Pending Complaints</div>
                <div class="fs-3 fw-bold"><?= (int) $stats['pending_complaints'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Outstanding Dues</div>
                <div class="fs-3 fw-bold text-danger"><?= number_format((float) $stats['outstanding_amount'], 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Income (This Month)</div>
                <div class="fs-3 fw-bold text-success"><?= number_format((float) $stats['income_this_month'], 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Expenses (This Month)</div>
                <div class="fs-3 fw-bold text-warning"><?= number_format((float) $stats['expenses_this_month'], 2) ?></div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
