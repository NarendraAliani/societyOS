<?php
$pageTitle = 'My Vehicles';
ob_start();
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">My Home</div>
            <h1 class="h3 mb-1">My Vehicles</h1>
            <p class="resident-subtitle mb-0">Vehicles registered against your resident profile.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addVehicleModal"><i class="fa-solid fa-plus me-1"></i>Add Vehicle</button>
            <span class="resident-home-pill"><i class="fa-solid fa-house me-1"></i><?= htmlspecialchars(($member['wing_name'] ?? '') . '-' . ($member['flat_number'] ?? 'Home')) ?></span>
        </div>
    </div>
    <section class="resident-section-card">
        <div class="resident-section-title"><span><i class="fa-solid fa-car resident-section-icon"></i>Vehicles</span><span class="badge text-bg-secondary"><?= count($vehicles) ?></span></div>
        <?php if (!$vehicles): ?>
            <div class="resident-empty"><i class="fa-solid fa-car"></i><div>No vehicles are registered for your profile yet.</div></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table resident-table align-middle mb-0">
                    <thead><tr><th>Registration</th><th>Type</th><th>Make / Model</th><th>Color</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($vehicle['registration_number']) ?></td>
                            <td><?= htmlspecialchars(ucfirst((string) $vehicle['vehicle_type'])) ?></td>
                            <td><?= htmlspecialchars(trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')) ?: '-') ?></td>
                            <td><?= htmlspecialchars($vehicle['color'] ?: '-') ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#editVehicleModal<?= (int) $vehicle['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/resident/vehicles/<?= (int) $vehicle['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Remove this vehicle?');">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
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
<div class="modal fade app-form-modal" id="editVehicleModal<?= (int) $vehicle['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header"><div><div class="resident-eyebrow">Vehicle</div><h5 class="modal-title">Edit Vehicle</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="post" action="/resident/vehicles/<?= (int) $vehicle['id'] ?>">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Vehicle Type <span class="text-danger">*</span></label><select name="vehicle_type" class="form-select" required><option value="two_wheeler" <?= $vehicle['vehicle_type'] === 'two_wheeler' ? 'selected' : '' ?>>Two wheeler</option><option value="four_wheeler" <?= $vehicle['vehicle_type'] === 'four_wheeler' ? 'selected' : '' ?>>Four wheeler</option></select></div>
                <div class="col-md-6"><label class="form-label">Registration Number <span class="text-danger">*</span></label><input name="registration_number" class="form-control" value="<?= htmlspecialchars($vehicle['registration_number']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Make</label><input name="make" class="form-control" value="<?= htmlspecialchars($vehicle['make'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Model</label><input name="model" class="form-control" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Color</label><input name="color" class="form-control" value="<?= htmlspecialchars($vehicle['color'] ?? '') ?>"></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button></div>
        </form>
    </div></div>
</div>
<?php endforeach; ?>

<div class="modal fade app-form-modal" id="addVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header"><div><div class="resident-eyebrow">Vehicle</div><h5 class="modal-title">Add Vehicle</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="post" action="/resident/vehicles">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="modal-body"><p class="resident-form-note mb-4">Register a vehicle against your home. Registration numbers must be unique in the society.</p><div class="row g-3">
                <div class="col-md-6"><label class="form-label">Vehicle Type <span class="text-danger">*</span></label><select name="vehicle_type" class="form-select" required><option value="two_wheeler">Two wheeler</option><option value="four_wheeler">Four wheeler</option></select></div>
                <div class="col-md-6"><label class="form-label">Registration Number <span class="text-danger">*</span></label><input name="registration_number" class="form-control" placeholder="e.g. GJ01AB1234" required></div>
                <div class="col-md-4"><label class="form-label">Make</label><input name="make" class="form-control" placeholder="Make"></div>
                <div class="col-md-4"><label class="form-label">Model</label><input name="model" class="form-control" placeholder="Model"></div>
                <div class="col-md-4"><label class="form-label">Color</label><input name="color" class="form-control" placeholder="Color"></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus me-1"></i>Add Vehicle</button></div>
        </form>
    </div></div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
