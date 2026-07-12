<?php
$pageTitle = 'Income Report';
ob_start();
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<form method="get" action="/reports/income" class="row g-2 mb-3" style="max-width: 480px;">
    <div class="col-4"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>"></div>
    <div class="col-4"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>"></div>
    <div class="col-4"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>
<a href="/reports/income?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>&format=csv" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-download me-1"></i>Export CSV</a>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Category</th><th>Account</th><th>Description</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['income_date']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['account_name']) ?></td>
                    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                    <td class="text-end text-success fw-bold"><?= number_format((float) $row['amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No income in this range.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot><tr class="fw-bold"><td colspan="4">Total</td><td class="text-end"><?= number_format((float) $total, 2) ?></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
