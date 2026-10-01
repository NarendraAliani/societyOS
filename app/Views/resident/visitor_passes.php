<?php
$pageTitle = 'My Visitor Passes';
ob_start();
?>
<p><a href="/dashboard">&laquo; My Home</a></p>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Create Visitor Pass</h6>
                <p class="text-muted small">Generate a one-time token for a visitor. The gate can verify it from the Visitors module.</p>
                <form method="post" action="/resident/visitor-passes">
                    <?= AppHelpersCsrf::field() ?>
                    <div class="mb-3"><label class="form-label">Visitor Name *</label><input name="visitor_name" class="form-control" maxlength="150" required></div>
                    <div class="mb-3"><label class="form-label">Valid From *</label><input type="datetime-local" name="valid_from" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Valid Until *</label><input type="datetime-local" name="valid_until" class="form-control" required></div>
                    <button class="btn btn-primary w-100" type="submit">Create Pass</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Passes for <?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Token</th><th>Visitor</th><th>Valid Window</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($passes as $pass): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($pass['qr_token']) ?></td>
                                <td><?= htmlspecialchars($pass['visitor_name']) ?></td>
                                <td><small><?= htmlspecialchars($pass['valid_from']) ?> &rarr; <?= htmlspecialchars($pass['valid_until']) ?></small></td>
                                <td><?php if ($pass['used_at']): ?><span class="badge bg-secondary">Used</span><?php elseif (strtotime($pass['valid_until']) < time()): ?><span class="badge bg-danger">Expired</span><?php else: ?><span class="badge bg-success">Active</span><?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$passes): ?><tr><td colspan="4" class="text-center text-muted py-4">No visitor passes yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';