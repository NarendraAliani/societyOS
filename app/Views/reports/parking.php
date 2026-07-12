<?php
$pageTitle = 'Parking Report';
ob_start();
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<a href="/reports/parking?format=csv" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-download me-1"></i>Export CSV</a>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Slot</th><th>Type</th><th>Status</th><th>Flat</th><th>Vehicle</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['slot_number']) ?></td>
                    <td><?= $row['slot_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?></td>
                    <td><span class="badge bg-<?= $row['is_allocated'] ? 'success' : 'secondary' ?>"><?= $row['is_allocated'] ? 'Occupied' : 'Free' ?></span></td>
                    <td><?= $row['flat_id'] ? htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) : '-' ?></td>
                    <td><?= htmlspecialchars($row['registration_number'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No parking slots configured.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
