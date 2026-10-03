<?php
ob_start();
?>
<p><a href="/accounting/reports">&laquo; Back to Reports</a></p>
<div class="alert alert-secondary small">
    This lists cash/bank account balances as of the chosen date. It isn't a full double-entry trial balance
    (income/expense/receivable accounts aren't part of this list) — this app doesn't maintain a complete chart of accounts.
</div>
<form method="get" action="/accounting/reports/trial-balance" class="row g-2 mb-3" style="max-width: 320px;">
    <div class="col-8"><input type="date" name="as_of" class="form-control form-control-sm" value="<?= htmlspecialchars($asOf) ?>"></div>
    <div class="col-4"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6>As of <?= htmlspecialchars($asOf) ?></h6>
        <table class="table table-hover align-middle">
            <thead><tr><th>Account</th><th>Type</th><th class="text-end">Balance</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><span class="badge bg-<?= $row['account_type'] === 'bank' ? 'info' : 'secondary' ?>"><?= ucfirst($row['account_type']) ?></span></td>
                    <td class="text-end"><?= number_format($row['balance'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="3" class="text-center text-muted py-4">No accounts set up yet.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot><tr class="fw-bold"><td colspan="2">Total</td><td class="text-end"><?= number_format($total, 2) ?></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
