<?php
$pageTitle = 'Occupancy Report';
ob_start();
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<p class="text-muted"><?= count($rows) ?> flats total &middot; <?= $vacant ?> vacant</p>
<a href="/reports/occupancy?format=csv" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-download me-1"></i>Export CSV</a>

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
