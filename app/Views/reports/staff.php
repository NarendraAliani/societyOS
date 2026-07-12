<?php
$pageTitle = 'Staff Report';
ob_start();
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<a href="/reports/staff?format=csv" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-download me-1"></i>Export CSV</a>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Name</th><th>Designation</th><th>Phone</th><th>Joined</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['designation'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['joining_date'] ?? '-') ?></td>
                    <td><span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($row['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No staff records.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
