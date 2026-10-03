<?php
$pageTitle = 'Occupancy Report';
ob_start();
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<p class="text-muted"><?= count($rows) ?> flats total &middot; <?= $vacant ?> vacant</p>
<div class="btn-group mb-3">
    <a href="/reports/occupancy?format=csv" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>CSV</a>
    <a href="/reports/occupancy?format=pdf" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
    <a href="/reports/occupancy?format=xlsx" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Excel</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Wing</th><th>Flat</th><th>Type</th><th>Occupancy</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['wing_name']) ?></td>
                    <td><?= htmlspecialchars($row['flat_number']) ?></td>
                    <td><?= htmlspecialchars($row['flat_type'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= $row['occupancy_status'] === 'vacant' ? 'secondary' : 'success' ?>"><?= ucfirst(str_replace('_', ' ', $row['occupancy_status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No flats configured.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
