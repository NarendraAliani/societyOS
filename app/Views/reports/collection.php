<?php
$pageTitle = 'Collection Report';
ob_start();
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<form method="get" action="/reports/collection" class="row g-2 mb-3" style="max-width: 480px;">
    <div class="col-4"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>"></div>
    <div class="col-4"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>"></div>
    <div class="col-4"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>
<a href="/reports/collection?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>&format=csv" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-download me-1"></i>Export CSV</a>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Bill No</th><th>Flat</th><th>Mode</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['paid_at']) ?></td>
                    <td><?= htmlspecialchars($row['bill_number']) ?></td>
                    <td><?= htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) ?></td>
                    <td><?= htmlspecialchars(str_replace('_', ' ', $row['payment_mode'])) ?></td>
                    <td><?= htmlspecialchars($row['reference_number'] ?? '-') ?></td>
                    <td class="text-end"><?= number_format((float) $row['amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No collections in this range.</td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($rows)): ?>
            <tfoot><tr class="fw-bold"><td colspan="5">Total</td><td class="text-end"><?= number_format((float) $total, 2) ?></td></tr></tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
