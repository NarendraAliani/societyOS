<?php
$pageTitle = 'Visitor Passes';
ob_start();
?>
<p><a href="/visitors">&laquo; Back to Visitor Register</a></p>

<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6>Verify / Check-in a Pass</h6>
                <p class="text-muted small">Gate security: enter the pass token the visitor presents.</p>
                <form method="post" action="/visitors/passes/verify">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="input-group">
                        <input type="text" name="token" class="form-control text-uppercase" placeholder="e.g. A1B2C3D4" required>
                        <button type="submit" class="btn btn-primary">Verify</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Create Pass</h6>
                <p class="text-muted small">Residents: pre-authorize an expected visitor.</p>
                <form method="post" action="/visitors/passes">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Flat *</label>
                        <select name="flat_id" class="form-select" required>
                            <option value="">Select flat</option>
                            <?php foreach ($flats as $flat): ?>
                                <option value="<?= (int) $flat['id'] ?>"><?= htmlspecialchars($flat['wing_name'] . '-' . $flat['flat_number']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Visitor Name *</label>
                        <input type="text" name="visitor_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valid From *</label>
                        <input type="datetime-local" name="valid_from" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valid Until *</label>
                        <input type="datetime-local" name="valid_until" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Pass</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Token</th><th>Visitor</th><th>Flat</th><th>Valid Window</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($passes as $pass): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($pass['qr_token']) ?></td>
                            <td><?= htmlspecialchars($pass['visitor_name']) ?></td>
                            <td><?= htmlspecialchars($pass['wing_name'] . '-' . $pass['flat_number']) ?></td>
                            <td><small><?= htmlspecialchars($pass['valid_from']) ?> &rarr; <?= htmlspecialchars($pass['valid_until']) ?></small></td>
                            <td>
                                <?php if ($pass['used_at']): ?>
                                    <span class="badge bg-secondary">Used</span>
                                <?php elseif (strtotime($pass['valid_until']) < time()): ?>
                                    <span class="badge bg-danger">Expired</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($passes)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No passes issued yet.</td></tr>
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
