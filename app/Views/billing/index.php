<?php
$pageTitle = 'Maintenance Bills';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Maintenance Bills</h5>
    <div>
        <a href="/billing/defaulters" class="btn btn-outline-danger btn-sm me-2"><i class="fa-solid fa-triangle-exclamation me-1"></i>Defaulters</a>
        <a href="/billing/generate" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Generate Bills</a>
    </div>
</div>

<form method="get" action="/billing" class="mb-3" style="max-width: 240px;">
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach (['unpaid', 'partially_paid', 'paid', 'overdue'] as $s): ?>
            <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Bill #</th><th>Flat</th><th>Period</th><th>Due</th><th>Total</th><th>Paid</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($bills as $bill): ?>
                <tr>
                    <td><a href="/billing/<?= (int) $bill['id'] ?>"><?= htmlspecialchars($bill['bill_number']) ?></a></td>
                    <td><?= htmlspecialchars($bill['wing_name'] . '-' . $bill['flat_number']) ?></td>
                    <td><?= htmlspecialchars($bill['bill_period_start']) ?> &rarr; <?= htmlspecialchars($bill['bill_period_end']) ?></td>
                    <td><?= htmlspecialchars($bill['due_date']) ?></td>
                    <td><?= number_format((float) $bill['total_amount'], 2) ?></td>
                    <td><?= number_format((float) $bill['paid_amount'], 2) ?></td>
                    <td>
                        <?php
                        $badge = match ($bill['status']) {
                            'paid' => 'success',
                            'partially_paid' => 'warning',
                            'overdue' => 'danger',
                            default => 'secondary',
                        };
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $bill['status'])) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($bills)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No bills yet. Generate the first billing cycle.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
