<?php
$pageTitle = 'Asset Report';
ob_start();
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<div class="btn-group mb-3">
    <a href="/reports/assets?format=csv" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>CSV</a>
    <a href="/reports/assets?format=pdf" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
    <a href="/reports/assets?format=xlsx" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Excel</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Name</th><th>Category</th><th>Location</th><th class="text-end">Cost</th><th>Warranty</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= htmlspecialchars($row['location'] ?? '-') ?></td>
                    <td class="text-end"><?= $row['purchase_cost'] !== null ? number_format((float) $row['purchase_cost'], 2) : '-' ?></td>
                    <td><?= htmlspecialchars($row['warranty_expiry'] ?? '-') ?></td>
                    <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $row['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No assets registered.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
