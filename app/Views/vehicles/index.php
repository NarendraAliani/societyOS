<?php
$pageTitle = 'Vehicles';
ob_start();
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">Society Management</div>
            <h1 class="h3 mb-1">Vehicles</h1>
            <p class="resident-subtitle mb-0">Manage resident vehicles registered in the society.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a href="/vehicles/parking" class="btn btn-outline-secondary"><i class="fa-solid fa-square-parking me-1"></i>Parking</a>
            <a href="/vehicles/create" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Add Vehicle</a>
        </div>
    </div>

    <section class="resident-section-card">
        <div class="resident-section-title">
            <span><i class="fa-solid fa-car resident-section-icon"></i>Registered Vehicles</span>
            <span class="badge text-bg-secondary"><?= count($vehicles) ?></span>
        </div>
        <?php if (!$vehicles): ?>
            <div class="resident-empty"><i class="fa-solid fa-car"></i><div>No vehicles registered yet.</div></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table resident-table align-middle mb-0">
                    <thead><tr><th>Registration</th><th>Type</th><th>Make / Model</th><th>Resident</th><th>Flat</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($vehicle['registration_number']) ?></td>
                            <td><span class="badge text-bg-<?= $vehicle['vehicle_type'] === 'two_wheeler' ? 'info' : 'primary' ?>"><?= $vehicle['vehicle_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?></span></td>
                            <td><?= htmlspecialchars(trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')) ?: '-') ?></td>
                            <td><?= htmlspecialchars($vehicle['member_name']) ?></td>
                            <td><?= htmlspecialchars($vehicle['wing_name'] . '-' . $vehicle['flat_number']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#editVehicleModal<?= (int) $vehicle['id'] ?>" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>/delete" onsubmit="return confirm('Remove this vehicle?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Remove"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php foreach ($vehicles as $vehicle): ?>
<div class="modal fade app-form-modal" id="editVehicleModal<?= (int) $vehicle['id'] ?>" tabindex="-1" aria-labelledby="editVehicleModalLabel<?= (int) $vehicle['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="resident-eyebrow">Vehicle</div>
                    <h5 class="modal-title" id="editVehicleModalLabel<?= (int) $vehicle['id'] ?>">Edit Vehicle</h5>
                    <div class="small text-muted"><?= htmlspecialchars($vehicle['member_name'] . ' · ' . $vehicle['wing_name'] . '-' . $vehicle['flat_number']) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>">
                <?= \App\Helpers\Csrf::field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                            <select name="vehicle_type" class="form-select" required>
                                <option value="four_wheeler" <?= $vehicle['vehicle_type'] === 'four_wheeler' ? 'selected' : '' ?>>4-Wheeler</option>
                                <option value="two_wheeler" <?= $vehicle['vehicle_type'] === 'two_wheeler' ? 'selected' : '' ?>>2-Wheeler</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                            <input type="text" name="registration_number" class="form-control text-uppercase" value="<?= htmlspecialchars($vehicle['registration_number']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Make</label>
                            <input type="text" name="make" class="form-control" value="<?= htmlspecialchars($vehicle['make'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($vehicle['color'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
