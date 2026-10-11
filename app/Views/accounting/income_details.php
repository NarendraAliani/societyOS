<?php
ob_start();
?>
<p><a href="/dashboard">&laquo; Back to Dashboard</a></p>
<p class="text-muted">This breakdown matches the Dashboard's <strong>Income (This Month)</strong> calculation: manually recorded income plus confirmed maintenance payments. Pending payment proofs are excluded until approved.</p>

<form method="get" action="/accounting/income-details" class="row g-2 align-items-end mb-4">
    <div class="col-sm-4 col-md-3">
        <label for="income-from" class="form-label">From</label>
        <input id="income-from" type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>" required>
    </div>
    <div class="col-sm-4 col-md-3">
        <label for="income-to" class="form-label">To</label>
        <input id="income-to" type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>" required>
    </div>
    <div class="col-sm-4 col-md-2"><button type="submit" class="btn btn-primary w-100">Apply</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="small text-muted">Manual Income</div>
            <div class="fs-4 fw-bold"><?= number_format($manualTotal, 2) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="small text-muted">Confirmed Maintenance Payments</div>
            <div class="fs-4 fw-bold text-success"><?= number_format($paymentTotal, 2) ?></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <div class="small text-muted">Total Income</div>
            <div class="fs-4 fw-bold text-success"><?= number_format($totalIncome, 2) ?></div>
            <div class="small text-muted">Selected date range</div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Income Calculation Details</h5>
            <a href="/accounting/income" class="btn btn-sm btn-outline-primary">Manage Manual Income</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Date</th><th>Type</th><th>Source / Category</th><th>Details</th><th>Account / Flat</th><th class="text-end">Amount</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $row['entry_date']) ?></td>
                        <td><span class="badge bg-<?= $row['entry_type'] === 'Confirmed payment' ? 'success' : 'secondary' ?>"><?= htmlspecialchars((string) $row['entry_type']) ?></span></td>
                        <td><?= htmlspecialchars((string) $row['source']) ?></td>
                        <td>
                            <?= htmlspecialchars((string) ($row['description'] ?? '-')) ?>
                            <?php if (!empty($row['payment_mode'])): ?><div class="small text-muted"><?= htmlspecialchars((string) $row['payment_mode']) ?><?= !empty($row['reference_number']) ? ' · Ref: ' . htmlspecialchars((string) $row['reference_number']) : '' ?></div><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string) ($row['account_name'] ?: (!empty($row['flat_number']) ? 'Flat ' . $row['flat_number'] : '-'))) ?></td>
                        <td class="text-end fw-bold text-success"><?= number_format((float) $row['amount'], 2) ?></td>
                        <td class="text-nowrap">
                            <?php if ($row['entry_type'] === 'Manual income'): ?>
                                <a class="btn btn-sm btn-outline-primary" href="/accounting/income/<?= (int) $row['income_id'] ?>/edit">Edit</a>
                                <form method="post" action="/accounting/income/<?= (int) $row['income_id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this manual income entry? Its linked ledger entry will also be removed.');">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Managed in Payments</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No income entries or confirmed payments in this date range.</td></tr>
                <?php endif; ?>
                </tbody>
                <tfoot><tr class="fw-bold"><td colspan="5">Total Income</td><td class="text-end text-success"><?= number_format($totalIncome, 2) ?></td><td></td></tr></tfoot>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
