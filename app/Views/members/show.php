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

        <div class="card border-0 shadow-sm mb-3">
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

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Documents</h6>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($documents as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <a href="/documents/<?= (int) $doc['id'] ?>/file" target="_blank"><?= htmlspecialchars($doc['title']) ?></a>
                                <small class="text-muted">
                                    <?= htmlspecialchars(strtoupper($doc['file_type'] ?? '')) ?>
                                    &middot; <?= htmlspecialchars($doc['created_at']) ?>
                                    <?php if ($doc['uploaded_by_name']): ?>&middot; by <?= htmlspecialchars($doc['uploaded_by_name']) ?><?php endif; ?>
                                </small>
                            </span>
                            <form method="post" action="/documents/<?= (int) $doc['id'] ?>/delete" onsubmit="return confirm('Remove this document?');">
                                <?= \App\Helpers\Csrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($documents)): ?>
                        <li class="list-group-item text-muted">None uploaded.</li>
                    <?php endif; ?>
                </ul>
                <form method="post" action="/members/<?= (int) $member['id'] ?>/documents" enctype="multipart/form-data" class="row g-2">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="col-5"><input type="text" name="title" class="form-control form-control-sm" placeholder="Title (e.g. Aadhar Card)" required></div>
                    <div class="col-5"><input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="form-control form-control-sm" required></div>
                    <div class="col-2"><button type="submit" class="btn btn-sm btn-primary w-100">Upload</button></div>
                </form>
                <div class="form-text mt-1">JPG, PNG, or PDF, up to <?= (int) config()['upload_max_size_mb'] ?> MB.</div>
            </div>
        </div>

        <?php if ($member['member_type'] === 'tenant'): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6>Lease Details</h6>
                <?php if ($tenant): ?>
                    <?php $ownerLabel = null; foreach ($ownerCandidates as $oc) { if ((int) $oc['id'] === (int) $tenant['owner_member_id']) { $ownerLabel = $oc['name']; } } ?>
                    <?php if ($tenant['lease_end']): ?>
                        <?php $daysLeft = (int) ((strtotime($tenant['lease_end']) - strtotime('today')) / 86400); ?>
                        <?php if ($daysLeft < 0): ?>
                            <span class="badge bg-danger mb-2">Lease expired <?= abs($daysLeft) ?> day<?= abs($daysLeft) !== 1 ? 's' : '' ?> ago</span>
                        <?php elseif ($daysLeft <= 30): ?>
                            <span class="badge bg-warning text-dark mb-2"><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?> left</span>
                        <?php else: ?>
                            <span class="badge bg-success mb-2">Active</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <form method="post" action="/leases/<?= (int) $tenant['id'] ?>" enctype="multipart/form-data">
                        <?= \App\Helpers\Csrf::field() ?>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Flat Owner</label>
                                <select name="owner_member_id" class="form-select form-select-sm" required>
                                    <?php foreach ($ownerCandidates as $oc): ?>
                                        <option value="<?= (int) $oc['id'] ?>" <?= (int) $oc['id'] === (int) $tenant['owner_member_id'] ? 'selected' : '' ?>><?= htmlspecialchars($oc['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Lease Start</label>
                                <input type="date" name="lease_start" class="form-control form-control-sm" value="<?= htmlspecialchars($tenant['lease_start'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Lease End</label>
                                <input type="date" name="lease_end" class="form-control form-control-sm" value="<?= htmlspecialchars($tenant['lease_end'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Agreement Document</label>
                                <?php if ($tenant['agreement_doc_path']): ?>
                                    <a href="/leases/<?= (int) $tenant['id'] ?>/agreement" target="_blank" class="d-block small mb-1">View current agreement</a>
                                <?php endif; ?>
                                <input type="file" name="agreement_doc" accept=".jpg,.jpeg,.png,.pdf" class="form-control form-control-sm">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mt-2">Save Lease Details</button>
                    </form>
                <?php elseif (empty($ownerCandidates)): ?>
                    <p class="text-muted small mb-0">No active owner is on record for this flat yet. Add the flat's owner as a resident first, then lease details can be linked to them.</p>
                <?php else: ?>
                    <p class="text-muted small">Not set up yet.</p>
                    <form method="post" action="/members/<?= (int) $member['id'] ?>/lease" enctype="multipart/form-data">
                        <?= \App\Helpers\Csrf::field() ?>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Flat Owner</label>
                                <select name="owner_member_id" class="form-select form-select-sm" required>
                                    <?php foreach ($ownerCandidates as $oc): ?>
                                        <option value="<?= (int) $oc['id'] ?>"><?= htmlspecialchars($oc['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Lease Start</label>
                                <input type="date" name="lease_start" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Lease End</label>
                                <input type="date" name="lease_end" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Agreement Document</label>
                                <input type="file" name="agreement_doc" accept=".jpg,.jpeg,.png,.pdf" class="form-control form-control-sm">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mt-2">Save Lease Details</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
