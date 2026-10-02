<?php
$pageTitle = 'My Maintenance Bills';
ob_start();
?>
<p><a href="/dashboard">&laquo; My Home</a></p>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5>My Maintenance Bills</h5>
        <p class="text-muted small">Only bills belonging to your linked home are shown.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Bill #</th><th>Period</th><th>Due</th><th>Total</th><th>Paid</th><th>Outstanding</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($bills as $bill): ?>
                    <?php $outstanding = max(0, (float) $bill['total_amount'] - (float) $bill['paid_amount']); $badge = match ($bill['status']) { 'paid' => 'success', 'partially_paid' => 'warning', 'overdue' => 'danger', default => 'secondary' }; ?>
                    <tr>
                        <td><?= htmlspecialchars($bill['bill_number']) ?></td>
                        <td><?= htmlspecialchars($bill['bill_period_start']) ?> &rarr; <?= htmlspecialchars($bill['bill_period_end']) ?></td>
                        <td><?= htmlspecialchars($bill['due_date']) ?></td>
                        <td>₹<?= number_format((float) $bill['total_amount'], 2) ?></td>
                        <td>₹<?= number_format((float) $bill['paid_amount'], 2) ?></td>
                        <td>₹<?= number_format($outstanding, 2) ?></td>
                        <td><span class="badge bg-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $bill['status'])) ?></span></td><td><?php if ($outstanding > 0): ?><button type="button" class="btn btn-sm btn-primary pay-bill-btn" data-bill-id="<?= (int)$bill['id'] ?>" data-amount="<?= htmlspecialchars((string)$outstanding) ?>">Pay Online</button><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$bills): ?><tr><td colspan="8" class="text-center text-muted py-4">No bills yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';