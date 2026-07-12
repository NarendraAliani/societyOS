<?php
$pageTitle = 'Defaulter Report';
ob_start();
?>
<p><a href="/billing">&laquo; Back to Bills</a></p>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Flat</th><th>Bill #</th><th>Due Date</th><th>Days Overdue</th><th class="text-end">Outstanding</th></tr></thead>
            <tbody>
            <?php foreach ($defaulters as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) ?></td>
                    <td><a href="/billing/<?= (int) $row['id'] ?>"><?= htmlspecialchars($row['bill_number']) ?></a></td>
                    <td><?= htmlspecialchars($row['due_date']) ?></td>
                    <td><span class="badge bg-danger"><?= (int) $row['days_overdue'] ?> days</span></td>
                    <td class="text-end fw-bold"><?= number_format((float) $row['outstanding'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($defaulters)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No defaulters. Everyone is paid up.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
