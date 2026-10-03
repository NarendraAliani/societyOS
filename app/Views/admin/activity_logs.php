<?php
$pageTitle = 'Activity Logs';
ob_start();
?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Application Activity</h6>
                <p class="text-muted small">Records module actions logged via <code>activity_logs</code>. If empty, no module in this build currently writes to it yet — see the project decision log.</p>
                <table class="table table-sm">
                    <thead><tr><th>Time</th><th>User</th><th>Module</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($log['created_at']) ?></small></td>
                            <td><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                            <td><?= htmlspecialchars($log['module']) ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No activity logged yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Login History</h6>
                <table class="table table-sm">
                    <thead><tr><th>Time</th><th>Email</th><th>IP</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($loginHistory as $entry): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($entry['created_at']) ?></small></td>
                            <td><?= htmlspecialchars($entry['email_attempted']) ?></td>
                            <td><?= htmlspecialchars($entry['ip_address'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $entry['status'] === 'success' ? 'success' : 'danger' ?>"><?= ucfirst($entry['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($loginHistory)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No login attempts recorded yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
