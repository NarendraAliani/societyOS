<?php
$pageTitle = 'Complaint Report';
ob_start();
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<a href="/reports/complaints?format=csv" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-download me-1"></i>Export CSV</a>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Category</th><th class="text-end">Total</th><th class="text-end">Open</th><th class="text-end">In Progress</th><th class="text-end">Resolved</th><th class="text-end">Closed</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td class="text-end fw-bold"><?= (int) $row['total'] ?></td>
                    <td class="text-end"><?= (int) $row['open_count'] ?></td>
                    <td class="text-end"><?= (int) $row['in_progress_count'] ?></td>
                    <td class="text-end"><?= (int) $row['resolved_count'] ?></td>
                    <td class="text-end"><?= (int) $row['closed_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No complaints logged yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
