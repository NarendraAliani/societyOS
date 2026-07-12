<?php
$pageTitle = $asset['name'];
ob_start();
?>
<p><a href="/assets">&laquo; Back to Assets</a></p>
<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h6><?= htmlspecialchars($asset['name']) ?></h6>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-asset"><i class="fa-solid fa-pen"></i></button>
                </div>
                <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($asset['category_name']) ?></p>
                <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($asset['location'] ?? '-') ?></p>
                <p class="mb-1"><strong>Purchase Date:</strong> <?= htmlspecialchars($asset['purchase_date'] ?? '-') ?></p>
                <p class="mb-1"><strong>Purchase Cost:</strong> <?= $asset['purchase_cost'] !== null ? number_format((float) $asset['purchase_cost'], 2) : '-' ?></p>
                <p class="mb-1"><strong>Warranty Expiry:</strong> <?= htmlspecialchars($asset['warranty_expiry'] ?? '-') ?></p>
                <form method="post" action="/assets/<?= (int) $asset['id'] ?>/status" class="mt-2">
                    <?= \App\Helpers\Csrf::field() ?>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="active" <?= $asset['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="under_repair" <?= $asset['status'] === 'under_repair' ? 'selected' : '' ?>>Under Repair</option>
                        <option value="disposed" <?= $asset['status'] === 'disposed' ? 'selected' : '' ?>>Disposed</option>
                    </select>
                </form>

                <div class="collapse mt-3" id="edit-asset">
                    <form method="post" action="/assets/<?= (int) $asset['id'] ?>">
                        <?= \App\Helpers\Csrf::field() ?>
                        <div class="mb-2">
                            <label class="form-label small">Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($asset['name']) ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Category</label>
                            <select name="category_id" class="form-select form-select-sm" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (int) $category['id'] === (int) $asset['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Location</label>
                            <input type="text" name="location" class="form-control form-control-sm" value="<?= htmlspecialchars($asset['location'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control form-control-sm" value="<?= htmlspecialchars($asset['purchase_date'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Purchase Cost</label>
                            <input type="number" step="0.01" name="purchase_cost" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($asset['purchase_cost'] ?? '')) ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Warranty Expiry</label>
                            <input type="date" name="warranty_expiry" class="form-control form-control-sm" value="<?= htmlspecialchars($asset['warranty_expiry'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6>Add AMC Record</h6>
                <form method="post" action="/assets/<?= (int) $asset['id'] ?>/amc">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-2">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= (int) $vendor['id'] ?>"><?= htmlspecialchars($vendor['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Start *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End *</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Cost</label>
                        <input type="number" step="0.01" name="cost" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add AMC</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Log Service</h6>
                <form method="post" action="/assets/<?= (int) $asset['id'] ?>/service">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-2">
                        <label class="form-label">Service Date *</label>
                        <input type="date" name="service_date" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Cost</label>
                        <input type="number" step="0.01" name="cost" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Next Due Date</label>
                        <input type="date" name="next_due_date" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log Service</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6>AMC History</h6>
                <table class="table table-sm">
                    <thead><tr><th>Vendor</th><th>Start</th><th>End</th><th class="text-end">Cost</th></tr></thead>
                    <tbody>
                    <?php foreach ($amcRecords as $amc): ?>
                        <tr>
                            <td><?= htmlspecialchars($amc['vendor_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($amc['start_date']) ?></td>
                            <td><?= htmlspecialchars($amc['end_date']) ?></td>
                            <td class="text-end"><?= $amc['cost'] !== null ? number_format((float) $amc['cost'], 2) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($amcRecords)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No AMC records.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Service History</h6>
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Description</th><th class="text-end">Cost</th><th>Next Due</th></tr></thead>
                    <tbody>
                    <?php foreach ($serviceRecords as $service): ?>
                        <tr>
                            <td><?= htmlspecialchars($service['service_date']) ?></td>
                            <td><?= htmlspecialchars($service['description'] ?? '-') ?></td>
                            <td class="text-end"><?= $service['cost'] !== null ? number_format((float) $service['cost'], 2) : '-' ?></td>
                            <td><?= htmlspecialchars($service['next_due_date'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($serviceRecords)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No service records.</td></tr>
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
