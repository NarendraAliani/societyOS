<?php
ob_start();
?>
<p><a href="/accounting/reports">&laquo; Back to Reports</a></p>
<form method="get" action="/accounting/reports/income-expense-statement" class="row g-2 mb-3" style="max-width: 480px;">
    <div class="col-4"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>"></div>
    <div class="col-4"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>"></div>
    <div class="col-4"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-success">Income</h6>
                <table class="table table-sm">
                    <tbody>
                    <?php foreach ($incomeByCategory as $row): ?>
                        <tr><td><?= htmlspecialchars($row['category']) ?></td><td class="text-end"><?= number_format((float) $row['total'], 2) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($incomeByCategory)): ?>
                        <tr><td colspan="2" class="text-center text-muted py-3">No income in this range.</td></tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot><tr class="fw-bold text-success"><td>Total Income</td><td class="text-end"><?= number_format($totalIncome, 2) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-danger">Expenses</h6>
                <table class="table table-sm">
                    <tbody>
                    <?php foreach ($expenseByCategory as $row): ?>
                        <tr><td><?= htmlspecialchars($row['category']) ?></td><td class="text-end"><?= number_format((float) $row['total'], 2) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($expenseByCategory)): ?>
                        <tr><td colspan="2" class="text-center text-muted py-3">No expenses in this range.</td></tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot><tr class="fw-bold text-danger"><td>Total Expenses</td><td class="text-end"><?= number_format($totalExpense, 2) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Net Surplus / (Deficit)</h6>
        <span class="fs-4 fw-bold <?= $net >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($net, 2) ?></span>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
