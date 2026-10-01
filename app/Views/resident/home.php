<?php
$pageTitle = 'My Home';
ob_start();
?>
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
                <div class="d-flex justify-content-between"><h6>Latest Notices</h6><a href="/resident/notices">View all</a></div>
                <?php foreach ($notices as $notice): ?>
                    <div class="border-bottom py-2">
                        <div class="fw-semibold"><?= htmlspecialchars($notice['title']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($notice['published_at']) ?></div>
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