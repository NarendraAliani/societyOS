<?php
$pageTitle = $member['name'];
ob_start();
?>
<p><a href="/members">&laquo; Back to Residents</a></p>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Resident Details &mdash; <?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></h6>
                <form method="post" action="/members/<?= (int) $member['id'] ?>">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="member_type" class="form-select">
                                <option value="owner" <?= $member['member_type'] === 'owner' ? 'selected' : '' ?>>Owner</option>
                                <option value="tenant" <?= $member['member_type'] === 'tenant' ? 'selected' : '' ?>>Tenant</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $member['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $member['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($member['phone']) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" name="alternate_phone" class="form-control" value="<?= htmlspecialchars($member['alternate_phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save</button>
                </form>
                <form method="post" action="/members/<?= (int) $member['id'] ?>/delete" class="mt-2" onsubmit="return confirm('Remove this resident?');">
                    <?= \App\Helpers\Csrf::field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Remove Resident</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6>Family Members</h6>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($familyMembers as $fm): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <?= htmlspecialchars($fm['name']) ?>
                                <small class="text-muted">
                                    <?= htmlspecialchars($fm['relation'] ?? '') ?>
                                    <?php if ($fm['display_age'] !== null): ?>
                                        &middot; <?= (int) $fm['display_age'] ?> yrs<?= $fm['date_of_birth'] ? ' (DOB: ' . htmlspecialchars($fm['date_of_birth']) . ')' : '' ?>
                                    <?php endif; ?>
                                </small>
                            </span>
                            <form method="post" action="/family-members/<?= (int) $fm['id'] ?>/delete" onsubmit="return confirm('Remove?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($familyMembers)): ?>
                        <li class="list-group-item text-muted">None added.</li>
                    <?php endif; ?>
                </ul>
                <form method="post" action="/members/<?= (int) $member['id'] ?>/family-members" class="row g-2">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="col-4"><input type="text" name="name" class="form-control form-control-sm" placeholder="Name" required></div>
                    <div class="col-3"><input type="text" name="relation" class="form-control form-control-sm" placeholder="Relation"></div>
                    <div class="col-2">
                        <input type="date" name="date_of_birth" class="form-control form-control-sm" title="Date of birth (preferred — age is then always accurate)">
                    </div>
                    <div class="col-2">
                        <input type="number" name="age" class="form-control form-control-sm" placeholder="Age" title="Only used if DOB is left blank">
                    </div>
                    <div class="col-1"><button type="submit" class="btn btn-sm btn-primary w-100">Add</button></div>
                </form>
                <div class="form-text mt-1">Enter DOB for an always-accurate age, or just Age if the birth date isn't known.</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Vehicles</h6>
                    <a href="/vehicles/create?return_to_member=<?= (int) $member['id'] ?>" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>Add Vehicle</a>
                </div>
                <ul class="list-group list-group-flush mt-2">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <?= htmlspecialchars($vehicle['registration_number']) ?>
                                <small class="text-muted">
                                    <?= $vehicle['vehicle_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?>
                                    <?php if ($vehicle['make'] || $vehicle['model']): ?>
                                        &middot; <?= htmlspecialchars(trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? ''))) ?>
                                    <?php endif; ?>
                                </small>
                            </span>
                            <form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>/delete" onsubmit="return confirm('Remove this vehicle?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <input type="hidden" name="return_to_member" value="<?= (int) $member['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($vehicles)): ?>
                        <li class="list-group-item text-muted">None added.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Emergency Contacts</h6>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($emergencyContacts as $ec): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($ec['name']) ?> <small class="text-muted"><?= htmlspecialchars($ec['relation'] ?? '') ?> &middot; <?= htmlspecialchars($ec['phone']) ?></small></span>
                            <form method="post" action="/emergency-contacts/<?= (int) $ec['id'] ?>/delete" onsubmit="return confirm('Remove?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($emergencyContacts)): ?>
                        <li class="list-group-item text-muted">None added.</li>
                    <?php endif; ?>
                </ul>
                <form method="post" action="/members/<?= (int) $member['id'] ?>/emergency-contacts" class="row g-2">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="col-4"><input type="text" name="name" class="form-control form-control-sm" placeholder="Name" required></div>
                    <div class="col-3"><input type="text" name="relation" class="form-control form-control-sm" placeholder="Relation"></div>
                    <div class="col-3"><input type="text" name="phone" class="form-control form-control-sm" placeholder="Phone" required></div>
                    <div class="col-2"><button type="submit" class="btn btn-sm btn-primary w-100">Add</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
