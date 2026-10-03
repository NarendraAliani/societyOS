<?php
$pageTitle = 'Floor ' . $floor['floor_number'];
ob_start();
?>
<p><a href="/society/wings/<?= (int) $floor['wing_id'] ?>">&laquo; Back to Wing</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Flats on Floor <?= (int) $floor['floor_number'] ?></h6>
                <table class="table table-hover align-middle">
                    <thead><tr><th>Flat #</th><th>Type</th><th>Carpet Area</th><th>Occupancy</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($flats as $flat): ?>
                        <tr>
                            <td><?= htmlspecialchars($flat['flat_number']) ?></td>
                            <td><?= htmlspecialchars($flat['flat_type'] ?? '-') ?></td>
                            <td><?= $flat['carpet_area_sqft'] !== null ? number_format((float) $flat['carpet_area_sqft'], 2) . ' sqft' : '-' ?></td>
                            <td><span class="badge bg-<?= $flat['occupancy_status'] === 'vacant' ? 'secondary' : 'success' ?>"><?= htmlspecialchars(str_replace('_', ' ', $flat['occupancy_status'])) ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-flat-<?= (int) $flat['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/society/flats/<?= (int) $flat['id'] ?>/delete" onsubmit="return confirm('Delete this flat?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-flat-<?= (int) $flat['id'] ?>">
                            <td colspan="5">
                                <form method="post" action="/society/flats/<?= (int) $flat['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-3">
                                        <label class="form-label small">Flat Number</label>
                                        <input type="text" name="flat_number" class="form-control form-control-sm" value="<?= htmlspecialchars($flat['flat_number']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Type</label>
                                        <input type="text" name="flat_type" class="form-control form-control-sm" value="<?= htmlspecialchars($flat['flat_type'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Carpet Area</label>
                                        <input type="number" step="0.01" name="carpet_area_sqft" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($flat['carpet_area_sqft'] ?? '')) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Occupancy</label>
                                        <select name="occupancy_status" class="form-select form-select-sm">
                                            <option value="vacant" <?= $flat['occupancy_status'] === 'vacant' ? 'selected' : '' ?>>Vacant</option>
                                            <option value="owner_occupied" <?= $flat['occupancy_status'] === 'owner_occupied' ? 'selected' : '' ?>>Owner Occupied</option>
                                            <option value="tenant_occupied" <?= $flat['occupancy_status'] === 'tenant_occupied' ? 'selected' : '' ?>>Tenant Occupied</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($flats)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No flats yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Flat</h6>
                <form method="post" action="/society/flats">
                    <?= \App\Helpers\Csrf::field() ?>
                    <input type="hidden" name="floor_id" value="<?= (int) $floor['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Flat Number</label>
                        <input type="text" name="flat_number" class="form-control" placeholder="e.g. 101 (no wing prefix)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <input type="text" name="flat_type" class="form-control" placeholder="e.g. 2BHK">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Carpet Area (sqft)</label>
                        <input type="number" step="0.01" name="carpet_area_sqft" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Flat</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
