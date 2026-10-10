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
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1">Welcome, <?= htmlspecialchars($member['name']) ?></h5>
        <div class="text-muted">Home: <?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?> · <?= htmlspecialchars(ucfirst($member['member_type'])) ?></div>
    </div>
    <a href="/resident/visitor-passes" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus me-1"></i>Visitor Pass</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Outstanding Maintenance</div><div class="fs-4 fw-semibold">₹<?= number_format($outstanding, 2) ?></div><a href="/resident/bills" class="small">View my bills</a></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">My Complaints</div><div class="fs-4 fw-semibold"><?= count($complaints) ?></div><a href="/resident/complaints" class="small">View / submit complaints</a></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Visitor Passes</div><div class="fs-4 fw-semibold"><?= count($passes) ?></div><a href="/resident/visitor-passes" class="small">Manage passes</a></div></div></div>
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