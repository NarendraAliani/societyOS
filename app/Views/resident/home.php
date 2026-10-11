<?php
$pageTitle = 'My Home';
ob_start();
?>
<style>
.notice-grid-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    column-gap: 1rem;
    row-gap: .25rem;
}
.notice-grid-details {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: .25rem;
}
.notice-grid-action {
    grid-column: 2;
    grid-row: 1;
    white-space: nowrap;
}
@media (max-width: 420px) {
    .notice-grid-row {
        grid-template-columns: minmax(0, 1fr);
        row-gap: .5rem;
    }
    .notice-grid-action {
        grid-column: 1;
        grid-row: auto;
        justify-self: start;
    }
}
</style>
<div class="resident-dashboard-header"><div><div class="small text-primary fw-bold text-uppercase mb-1">Resident portal</div><h3 class="resident-dashboard-title">Welcome, <?= htmlspecialchars($member['name']) ?></h3><div class="text-muted">Home: <?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?> · <?= htmlspecialchars(ucfirst($member['member_type'])) ?></div></div><div class="resident-dashboard-actions"><a href="/resident/bills" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-file-invoice-dollar me-1"></i>My Bills</a><a href="/resident/visitor-passes" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus me-1"></i>Visitor Pass</a></div></div>

<div class="row g-3 mb-4">
<div class="col-md-4"><a class="resident-summary-link" href="/resident/bills" aria-label="View outstanding maintenance and bills"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Outstanding Maintenance</div><div class="fs-4 fw-semibold">₹<?= number_format($outstanding, 2) ?></div><span class="small text-primary mt-2 d-inline-block">View my bills <i class="fa-solid fa-arrow-right ms-1"></i></span></div></div></a></div>
<div class="col-md-4"><a class="resident-summary-link" href="/resident/complaints" aria-label="View or submit complaints"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">My Complaints</div><div class="fs-4 fw-semibold"><?= count($complaints) ?></div><span class="small text-primary mt-2 d-inline-block">View / submit complaints <i class="fa-solid fa-arrow-right ms-1"></i></span></div></div></a></div>
<div class="col-md-4"><a class="resident-summary-link" href="/resident/visitor-passes" aria-label="Manage visitor passes"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Visitor Passes</div><div class="fs-4 fw-semibold"><?= count($passes) ?></div><span class="small text-primary mt-2 d-inline-block">Manage passes <i class="fa-solid fa-arrow-right ms-1"></i></span></div></div></a></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between"><h6>Recent Bills</h6><a href="/resident/bills">View all</a></div>
                <table class="table table-sm align-middle">
                    <thead><tr><th>Bill</th><th>Due</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($bills, 0, 5) as $bill): ?>
                        <?php $badge = match ($bill['status']) { 'paid' => 'success', 'partially_paid' => 'warning', 'overdue' => 'danger', default => 'secondary' }; ?>
                        <tr><td><?= htmlspecialchars($bill['bill_number']) ?></td><td><?= htmlspecialchars($bill['due_date']) ?></td><td>₹<?= number_format((float) $bill['total_amount'], 2) ?></td><td><span class="badge bg-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $bill['status'])) ?></span></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$bills): ?><tr><td colspan="4" class="text-muted text-center py-3">No bills yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Latest Notices</h6>
                    <a href="/resident/notices" class="small">View all</a>
                </div>
                <?php foreach ($notices as $notice): ?>
                    <div class="notice-grid-row border-bottom py-2">
                        <div class="notice-grid-details">
                            <span class="fw-semibold text-break"><?= htmlspecialchars($notice['title']) ?></span>
                            <span class="small text-muted text-nowrap"><?= htmlspecialchars($notice['published_at']) ?></span>
                        </div>
                        <a class="btn btn-outline-primary btn-sm notice-grid-action" href="/resident/notices/<?= (int) $notice['id'] ?>"><i class="fa-solid fa-eye me-1"></i>View <?= $notice['notice_type'] === 'circular' ? 'Circular' : 'Notice' ?></a>
                    </div>
                <?php endforeach; ?>
                <?php if (!$notices): ?><div class="text-muted">No active notices.</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';