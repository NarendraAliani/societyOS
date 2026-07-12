<?php
ob_start();
?>
<p><a href="/accounting/accounts">&laquo; Back to Accounts</a></p>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6>Trial Balance</h6>
                <p class="text-muted small">Account balances as of a chosen date. Not a full double-entry trial balance — this app tracks cash/bank accounts only, not a complete chart of accounts.</p>
                <a href="/accounting/reports/trial-balance" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6>Income &amp; Expense Statement</h6>
                <p class="text-muted small">Income and expenses by category over a date range, with net surplus/deficit.</p>
                <a href="/accounting/reports/income-expense-statement" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6>Balance Sheet</h6>
                <p class="text-muted small">Today's Assets (cash/bank + outstanding dues) vs. Accumulated Fund. No liabilities are tracked in this schema, so it balances by construction, not as an independent check.</p>
                <a href="/accounting/reports/balance-sheet" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
