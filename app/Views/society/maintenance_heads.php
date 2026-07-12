<?php
$pageTitle = 'Maintenance Configuration';
ob_start();
?>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Head</th><th>Type</th><th>Current Amount</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($heads as $head): ?>
                        <tr>
                            <td><?= htmlspecialchars($head['name']) ?></td>
                            <td><?= $head['calculation_type'] === 'per_sqft' ? 'Per Sqft' : 'Fixed' ?></td>
                            <td>
                                <?= $head['current_amount'] !== null ? number_format((float) $head['current_amount'], 2) : '<span class="text-muted">Not yet effective</span>' ?>
                                <?php if ((int) $head['scheduled_count'] > 0): ?>
                                    <br><a href="/society/maintenance-heads/<?= (int) $head['id'] ?>" class="badge bg-info text-decoration-none"><?= (int) $head['scheduled_count'] ?> upcoming change<?= (int) $head['scheduled_count'] > 1 ? 's' : '' ?></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="/society/maintenance-heads/<?= (int) $head['id'] ?>/toggle" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm <?= $head['is_active'] ? 'btn-success' : 'btn-outline-secondary' ?>" type="submit">
                                        <?= $head['is_active'] ? 'Active' : 'Inactive' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="/society/maintenance-heads/<?= (int) $head['id'] ?>" class="btn btn-sm btn-outline-primary">Manage Rates</a>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-head-<?= (int) $head['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/society/maintenance-heads/<?= (int) $head['id'] ?>/delete" onsubmit="return confirm('Delete this maintenance head and all its rate history?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-head-<?= (int) $head['id'] ?>">
                            <td colspan="5">
                                <form method="post" action="/society/maintenance-heads/<?= (int) $head['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-5">
                                        <label class="form-label small">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($head['name']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Type</label>
                                        <select name="calculation_type" class="form-select form-select-sm">
                                            <option value="fixed" <?= $head['calculation_type'] === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                                            <option value="per_sqft" <?= $head['calculation_type'] === 'per_sqft' ? 'selected' : '' ?>>Per Sqft</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">To change the amount, use "Manage Rates" — it's scheduled by effective date, not edited in place.</small>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($heads)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No maintenance heads configured yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Maintenance Head</h6>
                <form method="post" action="/society/maintenance-heads">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Sinking Fund" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Calculation Type</label>
                        <select name="calculation_type" class="form-select">
                            <option value="fixed">Fixed</option>
                            <option value="per_sqft">Per Sqft</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                        <div class="form-text">Effective immediately. Future rate changes are scheduled from the head's detail page.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Head</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
