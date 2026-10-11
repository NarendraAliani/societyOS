<?php
ob_start();
?>
<p><a href="/accounting/reports">&laquo; Back to Reports</a></p>
<div class="alert alert-secondary small">
    This app tracks no liabilities (no loans/payables). Liabilities always show ₹0, so Accumulated Fund equals total
    Assets by construction — a snapshot of today's state, not an independent balance check.
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Assets</h6>
                <table class="table table-sm">
                    <tbody>
                    <?php foreach ($accounts as $acc): ?>
                        <tr>
                            <td><?= htmlspecialchars($acc['name']) ?> <span class="badge bg-<?= $acc['account_type'] === 'bank' ? 'info' : 'secondary' ?>"><?= ucfirst($acc['account_type']) ?></span></td>
                            <td class="text-end"><?= number_format((float) $acc['current_balance'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td>Accounts Receivable <small class="text-muted">(outstanding maintenance dues)</small></td>
                        <td class="text-end"><?= number_format($receivables, 2) ?></td>
                    </tr>
                    </tbody>
                    <tfoot><tr class="fw-bold"><td>Total Assets</td><td class="text-end"><?= number_format($totalAssets, 2) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Liabilities &amp; Equity</h6>
                <table class="table table-sm">
                    <tbody>
                        <tr><td>Liabilities</td><td class="text-end">0.00</td></tr>
                        <tr><td>Accumulated Fund <small class="text-muted">(Equity)</small></td><td class="text-end"><?= number_format($totalAssets, 2) ?></td></tr>
                    </tbody>
                    <tfoot><tr class="fw-bold"><td>Total Liabilities &amp; Equity</td><td class="text-end"><?= number_format($totalAssets, 2) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
