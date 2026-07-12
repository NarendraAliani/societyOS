<?php
$pageTitle = 'Vehicles';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Vehicles</h5>
    <div>
        <a href="/vehicles/parking" class="btn btn-outline-secondary btn-sm me-2"><i class="fa-solid fa-square-parking me-1"></i>Parking</a>
        <a href="/vehicles/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add Vehicle</a>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Reg. No.</th><th>Type</th><th>Make/Model</th><th>Owner</th><th>Flat</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($vehicle['registration_number']) ?></td>
                    <td><span class="badge bg-<?= $vehicle['vehicle_type'] === 'two_wheeler' ? 'info' : 'primary' ?>"><?= $vehicle['vehicle_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?></span></td>
                    <td><?= htmlspecialchars(trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')) ?: '-') ?></td>
                    <td><?= htmlspecialchars($vehicle['member_name']) ?></td>
                    <td><?= htmlspecialchars($vehicle['wing_name'] . '-' . $vehicle['flat_number']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-vehicle-<?= (int) $vehicle['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                        <form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>/delete" onsubmit="return confirm('Remove this vehicle?');" class="d-inline">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <tr class="collapse" id="edit-vehicle-<?= (int) $vehicle['id'] ?>">
                    <td colspan="6">
                        <form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>" class="row g-2 align-items-end p-2">
                            <?= \App\Helpers\Csrf::field() ?>
                            <div class="col-md-2">
                                <label class="form-label small">Reg. No.</label>
                                <input type="text" name="registration_number" class="form-control form-control-sm text-uppercase" value="<?= htmlspecialchars($vehicle['registration_number']) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Type</label>
                                <select name="vehicle_type" class="form-select form-select-sm">
                                    <option value="four_wheeler" <?= $vehicle['vehicle_type'] === 'four_wheeler' ? 'selected' : '' ?>>4-Wheeler</option>
                                    <option value="two_wheeler" <?= $vehicle['vehicle_type'] === 'two_wheeler' ? 'selected' : '' ?>>2-Wheeler</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Make</label>
                                <input type="text" name="make" class="form-control form-control-sm" value="<?= htmlspecialchars($vehicle['make'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Model</label>
                                <input type="text" name="model" class="form-control form-control-sm" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Color</label>
                                <input type="text" name="color" class="form-control form-control-sm" value="<?= htmlspecialchars($vehicle['color'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($vehicles)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No vehicles registered yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
