<?php
$pageTitle = 'My Family';
ob_start();
?>
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-1">My Family</h5>
                <p class="text-muted mb-0"><?= htmlspecialchars($member['name']) ?> &mdash; <?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Family Members</h6>
                    <span class="badge bg-secondary"><?= count($familyMembers) ?></span>
                </div>
                <div class="list-group list-group-flush mb-3">
                    <?php foreach ($familyMembers as $fm): ?>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold"><?= htmlspecialchars($fm['name']) ?></span>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($fm['relation'] ?? 'Family member') ?>
                                    <?php if ($fm['display_age'] !== null): ?>
                                        &middot; <?= (int) $fm['display_age'] ?> yrs
                                    <?php endif; ?>
                                    <?php if (!empty($fm['phone'])): ?>
                                        &middot; <?= htmlspecialchars($fm['phone']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="post" action="/resident/family-members/<?= (int) $fm['id'] ?>/delete" onsubmit="return confirm('Remove this family member?');">
                                <?= AppHelpersCsrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Remove"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($familyMembers)): ?>
                        <div class="text-muted py-2">No family members added yet.</div>
                    <?php endif; ?>
                </div>

                <h6 class="border-top pt-3">Add Family Member</h6>
                <form method="post" action="/resident/family-members" class="row g-2">
                    <?= AppHelpersCsrf::field() ?>
                    <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                    <div class="col-md-3"><input type="text" name="relation" class="form-control" placeholder="Relation"></div>
                    <div class="col-md-3"><input type="date" name="date_of_birth" class="form-control" title="Date of birth"></div>
                    <div class="col-md-2"><input type="text" name="phone" class="form-control" placeholder="Phone"></div>
                    <div class="col-12"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus me-1"></i>Add Family Member</button></div>
                </form>
                <div class="form-text mt-2">Enter date of birth when known; age is calculated automatically.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Emergency Contacts</h6>
                    <span class="badge bg-secondary"><?= count($emergencyContacts) ?></span>
                </div>
                <div class="list-group list-group-flush mb-3">
                    <?php foreach ($emergencyContacts as $ec): ?>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold"><?= htmlspecialchars($ec['name']) ?></span>
                                <div class="small text-muted"><?= htmlspecialchars($ec['relation'] ?? 'Emergency contact') ?> &middot; <?= htmlspecialchars($ec['phone']) ?></div>
                            </div>
                            <form method="post" action="/resident/emergency-contacts/<?= (int) $ec['id'] ?>/delete" onsubmit="return confirm('Remove this emergency contact?');">
                                <?= AppHelpersCsrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit" title="Remove"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($emergencyContacts)): ?>
                        <div class="text-muted py-2">No emergency contacts added yet.</div>
                    <?php endif; ?>
                </div>

                <h6 class="border-top pt-3">Add Emergency Contact</h6>
                <form method="post" action="/resident/emergency-contacts" class="row g-2">
                    <?= AppHelpersCsrf::field() ?>
                    <div class="col-12"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                    <div class="col-6"><input type="text" name="relation" class="form-control" placeholder="Relation"></div>
                    <div class="col-6"><input type="text" name="phone" class="form-control" placeholder="Phone" required></div>
                    <div class="col-12"><button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-plus me-1"></i>Add Contact</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
