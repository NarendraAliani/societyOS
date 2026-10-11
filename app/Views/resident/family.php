<?php
$pageTitle = 'My Family';
ob_start();
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">Resident Services</div>
            <h4>My Family</h4>
            <p class="resident-subtitle">Keep your household and emergency contact information up to date.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addFamilyMemberModal">
                <i class="fa-solid fa-plus me-1"></i>Add Family Member
            </button>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#addEmergencyContactModal">
                <i class="fa-solid fa-phone-plus me-1"></i>Add Emergency Contact
            </button>
            <div class="resident-home-pill">
                <i class="fa-solid fa-house"></i>
                <span><?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm kpi-card kpi-primary resident-stat">
                <div class="card-body">
                    <div class="stat-label">Family Members</div>
                    <div class="stat-value"><?= count($familyMembers) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm kpi-card kpi-danger resident-stat">
                <div class="card-body">
                    <div class="stat-label">Emergency Contacts</div>
                    <div class="stat-value"><?= count($emergencyContacts) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4">
            <div class="card border-0 shadow-sm kpi-card kpi-info resident-stat">
                <div class="card-body">
                    <div class="stat-label">Resident / Home</div>
                    <div class="stat-value fs-5"><?= htmlspecialchars($member['name']) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <h5 class="resident-section-title">
                        <span class="resident-section-icon"><i class="fa-solid fa-people-roof"></i></span>
                        Family Members
                    </h5>
                    <span class="badge text-bg-secondary"><?= count($familyMembers) ?></span>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush resident-list">
                        <?php foreach ($familyMembers as $fm): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <div class="resident-section-icon"><i class="fa-solid fa-user"></i></div>
                                    <div class="min-w-0">
                                        <div class="resident-item-title text-truncate"><?= htmlspecialchars($fm['name']) ?></div>
                                        <div class="resident-item-meta">
                                            <?= htmlspecialchars($fm['relation'] ?? 'Family member') ?>
                                            <?php if ($fm['display_age'] !== null): ?> &middot; <?= (int) $fm['display_age'] ?> yrs<?php endif; ?>
                                            <?php if (!empty($fm['phone'])): ?> &middot; <?= htmlspecialchars($fm['phone']) ?><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-primary" type="button" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#editFamilyMemberModal<?= (int) $fm['id'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="post" action="/resident/family-members/<?= (int) $fm['id'] ?>/delete" onsubmit="return confirm('Remove this family member?');">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($familyMembers)): ?>
                            <div class="resident-empty py-4">
                                <i class="fa-solid fa-people-roof"></i>
                                No family members added yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <h5 class="resident-section-title">
                        <span class="resident-section-icon"><i class="fa-solid fa-phone-volume"></i></span>
                        Emergency Contacts
                    </h5>
                    <span class="badge text-bg-secondary"><?= count($emergencyContacts) ?></span>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush resident-list">
                        <?php foreach ($emergencyContacts as $ec): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <div class="resident-section-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div class="min-w-0">
                                        <div class="resident-item-title text-truncate"><?= htmlspecialchars($ec['name']) ?></div>
                                        <div class="resident-item-meta"><?= htmlspecialchars($ec['relation'] ?? 'Emergency contact') ?> &middot; <?= htmlspecialchars($ec['phone']) ?></div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-primary" type="button" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#editEmergencyContactModal<?= (int) $ec['id'] ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="post" action="/resident/emergency-contacts/<?= (int) $ec['id'] ?>/delete" onsubmit="return confirm('Remove this emergency contact?');">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Remove">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($emergencyContacts)): ?>
                            <div class="resident-empty py-4">
                                <i class="fa-solid fa-phone-slash"></i>
                                No emergency contacts added yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Family Member -->
<div class="modal fade app-form-modal" id="addFamilyMemberModal" tabindex="-1" aria-labelledby="addFamilyMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="resident-eyebrow">Family</div>
                    <h5 class="modal-title" id="addFamilyMemberModalLabel">Add Family Member</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/resident/family-members">
                <?= \App\Helpers\Csrf::field() ?>
                <div class="modal-body">
                    <p class="resident-form-note mb-4">Add household members so society records stay current.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Relation</label>
                            <input type="text" name="relation" class="form-control" placeholder="e.g. Spouse, Child">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus me-1"></i>Add Family Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Emergency Contact -->
<div class="modal fade app-form-modal" id="addEmergencyContactModal" tabindex="-1" aria-labelledby="addEmergencyContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="resident-eyebrow">Emergency Contact</div>
                    <h5 class="modal-title" id="addEmergencyContactModalLabel">Add Emergency Contact</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/resident/emergency-contacts">
                <?= \App\Helpers\Csrf::field() ?>
                <div class="modal-body">
                    <p class="resident-form-note mb-4">Keep at least one trusted contact available for urgent situations.</p>
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Relation</label>
                            <input type="text" name="relation" class="form-control" placeholder="Relation">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="Phone number" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus me-1"></i>Add Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($familyMembers as $fm): ?>
<div class="modal fade app-form-modal" id="editFamilyMemberModal<?= (int) $fm['id'] ?>" tabindex="-1" aria-labelledby="editFamilyMemberModalLabel<?= (int) $fm['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="resident-eyebrow">Family</div>
                    <h5 class="modal-title" id="editFamilyMemberModalLabel<?= (int) $fm['id'] ?>">Edit Family Member</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/resident/family-members">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $fm['id'] ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($fm['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Relation</label>
                            <input type="text" name="relation" class="form-control" value="<?= htmlspecialchars($fm['relation'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($fm['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($fm['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <p class="resident-form-note mt-3 mb-0">Age is calculated automatically from date of birth when provided.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php foreach ($emergencyContacts as $ec): ?>
<div class="modal fade app-form-modal" id="editEmergencyContactModal<?= (int) $ec['id'] ?>" tabindex="-1" aria-labelledby="editEmergencyContactModalLabel<?= (int) $ec['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="resident-eyebrow">Emergency Contact</div>
                    <h5 class="modal-title" id="editEmergencyContactModalLabel<?= (int) $ec['id'] ?>">Edit Emergency Contact</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/resident/emergency-contacts">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $ec['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($ec['name']) ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Relation</label>
                            <input type="text" name="relation" class="form-control" value="<?= htmlspecialchars($ec['relation'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($ec['phone']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
