<?php
$pageTitle = 'Defaulter Report';
ob_start();
?>
<p><a href="/billing">&laquo; Back to Bills</a></p>
<div class="btn-group mb-3">
    <a href="/reports/defaulters?format=csv" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>CSV</a>
    <a href="/reports/defaulters?format=pdf" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
    <a href="/reports/defaulters?format=xlsx" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Excel</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Flat</th><th>Bill #</th><th>Due Date</th><th>Days Overdue</th><th class="text-end">Outstanding</th><th class="text-end">Penalty</th><th class="text-end">Total Due</th></tr></thead>
            <tbody>
            <?php foreach ($defaulters as $row): ?>
                <?php $penaltyAmount = (float) ($row['penalty_amount'] ?? 0); ?>
                <tr>
                    <td><?= htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) ?></td>
                    <td><a href="/billing/<?= (int) $row['id'] ?>"><?= htmlspecialchars($row['bill_number']) ?></a></td>
                    <td><?= htmlspecialchars($row['due_date']) ?></td>
                    <td><span class="badge bg-danger"><?= (int) $row['days_overdue'] ?> days</span></td>
                    <td class="text-end"><?= number_format((float) $row['outstanding'], 2) ?></td>
                    <td class="text-end <?= $penaltyAmount > 0 ? 'text-warning' : 'text-muted' ?>"><?= number_format($penaltyAmount, 2) ?></td>
                    <td class="text-end fw-bold"><?= number_format((float) $row['outstanding'] + $penaltyAmount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($defaulters)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No defaulters. Everyone is paid up.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
