<?php
$pageTitle = $member['name'];
ob_start();
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">Member Management</div>
            <h4><?= htmlspecialchars($member['name']) ?></h4>
            <p class="resident-subtitle">
                <?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?>
                &middot; <?= htmlspecialchars(ucfirst($member['member_type'])) ?>
                &middot; <span class="badge text-bg-<?= $member['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($member['status'])) ?></span>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a href="/members" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Residents</a>
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editMemberModal">
                <i class="fa-solid fa-pen me-1"></i>Edit Resident
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-primary resident-stat"><div class="card-body">
                <div class="stat-label">Family Members</div><div class="stat-value"><?= count($familyMembers) ?></div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-info resident-stat"><div class="card-body">
                <div class="stat-label">Vehicles</div><div class="stat-value"><?= count($vehicles) ?></div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-danger resident-stat"><div class="card-body">
                <div class="stat-label">Emergency Contacts</div><div class="stat-value"><?= count($emergencyContacts) ?></div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-success resident-stat"><div class="card-body">
                <div class="stat-label">Documents</div><div class="stat-value"><?= count($documents) ?></div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="resident-section-title"><span class="resident-section-icon"><i class="fa-solid fa-people-roof"></i></span>Family Members</h5>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addFamilyModal"><i class="fa-solid fa-plus me-1"></i>Add</button>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush resident-list">
                        <?php foreach ($familyMembers as $fm): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="resident-item-title text-truncate"><?= htmlspecialchars($fm['name']) ?></div>
                                    <div class="resident-item-meta"><?= htmlspecialchars($fm['relation'] ?? 'Family member') ?><?php if ($fm['display_age'] !== null): ?> &middot; <?= (int) $fm['display_age'] ?> yrs<?php endif; ?></div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFamilyModal<?= (int) $fm['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="/family-members/<?= (int) $fm['id'] ?>/delete" onsubmit="return confirm('Remove this family member?');"><?= AppHelpersCsrf::field() ?><button class="btn btn-sm btn-outline-danger" title="Remove"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$familyMembers): ?><div class="resident-empty py-4"><i class="fa-solid fa-people-roof"></i>No family members added.</div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="resident-section-title"><span class="resident-section-icon"><i class="fa-solid fa-phone-volume"></i></span>Emergency Contacts</h5>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addEmergencyModal"><i class="fa-solid fa-plus me-1"></i>Add</button>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush resident-list">
                        <?php foreach ($emergencyContacts as $ec): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="resident-item-title text-truncate"><?= htmlspecialchars($ec['name']) ?></div>
                                    <div class="resident-item-meta"><?= htmlspecialchars($ec['relation'] ?? 'Emergency contact') ?> &middot; <?= htmlspecialchars($ec['phone']) ?></div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEmergencyModal<?= (int) $ec['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="/emergency-contacts/<?= (int) $ec['id'] ?>/delete" onsubmit="return confirm('Remove this emergency contact?');"><?= AppHelpersCsrf::field() ?><button class="btn btn-sm btn-outline-danger" title="Remove"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$emergencyContacts): ?><div class="resident-empty py-4"><i class="fa-solid fa-phone-slash"></i>No emergency contacts added.</div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="resident-section-title"><span class="resident-section-icon"><i class="fa-solid fa-car"></i></span>Vehicles</h5>
                    <a href="/vehicles/create?return_to_member=<?= (int) $member['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush resident-list">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="resident-item-title text-truncate"><?= htmlspecialchars($vehicle['registration_number']) ?></div>
                                    <div class="resident-item-meta"><?= $vehicle['vehicle_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?><?php if ($vehicle['make'] || $vehicle['model']): ?> &middot; <?= htmlspecialchars(trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? ''))) ?><?php endif; ?><?php if ($vehicle['color']): ?> &middot; <?= htmlspecialchars($vehicle['color']) ?><?php endif; ?></div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVehicleModal<?= (int) $vehicle['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>/delete" onsubmit="return confirm('Remove this vehicle?');"><?= AppHelpersCsrf::field() ?><input type="hidden" name="return_to_member" value="<?= (int) $member['id'] ?>"><button class="btn btn-sm btn-outline-danger" title="Remove"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$vehicles): ?><div class="resident-empty py-4"><i class="fa-solid fa-car"></i>No vehicles added.</div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="resident-section-title"><span class="resident-section-icon"><i class="fa-solid fa-folder-open"></i></span>Documents</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal"><i class="fa-solid fa-upload me-1"></i>Upload</button>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush resident-list">
                        <?php foreach ($documents as $doc): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="resident-item-title text-truncate"><?= htmlspecialchars($doc['title']) ?></div>
                                    <div class="resident-item-meta"><?= htmlspecialchars(strtoupper($doc['file_type'] ?? '')) ?> &middot; <?= htmlspecialchars($doc['created_at']) ?><?php if ($doc['uploaded_by_name']): ?> &middot; by <?= htmlspecialchars($doc['uploaded_by_name']) ?><?php endif; ?></div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <a class="btn btn-sm btn-outline-primary" href="/documents/<?= (int) $doc['id'] ?>/file" target="_blank" title="View"><i class="fa-solid fa-eye"></i></a>
                                    <form method="post" action="/documents/<?= (int) $doc['id'] ?>/delete" onsubmit="return confirm('Remove this document?');"><?= AppHelpersCsrf::field() ?><button class="btn btn-sm btn-outline-danger" title="Remove"><i class="fa-solid fa-trash"></i></button></form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$documents): ?><div class="resident-empty py-4"><i class="fa-solid fa-folder-open"></i>No documents uploaded.</div><?php endif; ?>
                    </div>
                    <div class="resident-form-note mt-3">JPG, PNG, or PDF, up to <?= (int) AppModelsSettings::get((int) $_SESSION['society_id'], 'upload_max_size_mb', config()['upload_max_size_mb']) ?> MB.</div>
                </div>
            </div>
        </div>

        <?php if ($member['member_type'] === 'tenant'): ?>
        <div class="col-12">
            <div class="card resident-section-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="resident-section-title"><span class="resident-section-icon"><i class="fa-solid fa-file-contract"></i></span>Lease Details</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#leaseModal"><i class="fa-solid fa-pen me-1"></i><?= $tenant ? 'Edit Lease' : 'Set Up Lease' ?></button>
                </div>
                <div class="card-body">
                    <?php if ($tenant): ?>
                        <?php $ownerLabel = null; foreach ($ownerCandidates as $oc) { if ((int) $oc['id'] === (int) $tenant['owner_member_id']) { $ownerLabel = $oc['name']; } } ?>
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3"><div class="resident-form-note">Flat Owner<strong class="d-block mt-1"><?= htmlspecialchars($ownerLabel ?? 'Not found') ?></strong></div></div>
                            <div class="col-sm-6 col-lg-3"><div class="resident-form-note">Lease Start<strong class="d-block mt-1"><?= htmlspecialchars($tenant['lease_start'] ?? '—') ?></strong></div></div>
                            <div class="col-sm-6 col-lg-3"><div class="resident-form-note">Lease End<strong class="d-block mt-1"><?= htmlspecialchars($tenant['lease_end'] ?? '—') ?></strong></div></div>
                            <div class="col-sm-6 col-lg-3"><div class="resident-form-note">Agreement<strong class="d-block mt-1"><?php if ($tenant['agreement_doc_path']): ?><a href="/leases/<?= (int) $tenant['id'] ?>/agreement" target="_blank">View document</a><?php else: ?>Not uploaded<?php endif; ?></strong></div></div>
                        </div>
                    <?php elseif (empty($ownerCandidates)): ?>
                        <div class="resident-empty py-3">No active owner is recorded for this flat yet.</div>
                    <?php else: ?>
                        <div class="resident-empty py-3">Lease details have not been set up yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-12">
            <div class="card resident-section-card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div><strong>Resident account</strong><div class="resident-item-meta"><?= htmlspecialchars($member['email'] ?? 'No email') ?> &middot; <?= htmlspecialchars($member['phone']) ?></div></div>
                    <form method="post" action="/members/<?= (int) $member['id'] ?>/delete" onsubmit="return confirm('Remove this resident? This action cannot be undone.');"><?= AppHelpersCsrf::field() ?><button class="btn btn-outline-danger btn-sm">Remove Resident</button></form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resident edit -->
<div class="modal fade app-form-modal" id="editMemberModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Member Management</div><h5 class="modal-title">Edit Resident</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/members/<?= (int) $member['id'] ?>"><div class="modal-body"><?= AppHelpersCsrf::field() ?><div class="row g-3">
<div class="col-md-6"><label class="form-label">Type</label><select name="member_type" class="form-select"><option value="owner" <?= $member['member_type']==='owner'?'selected':'' ?>>Owner</option><option value="tenant" <?= $member['member_type']==='tenant'?'selected':'' ?>>Tenant</option></select></div>
<div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $member['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $member['status']==='inactive'?'selected':'' ?>>Inactive</option></select></div>
<div class="col-12"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= htmlspecialchars($member['phone']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Alternate Phone</label><input name="alternate_phone" class="form-control" value="<?= htmlspecialchars($member['alternate_phone'] ?? '') ?>"></div>
<div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email'] ?? '') ?>"></div>
</div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div></form>
</div></div></div>

<!-- Add family -->
<div class="modal fade app-form-modal" id="addFamilyModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Family</div><h5 class="modal-title">Add Family Member</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/members/<?= (int) $member['id'] ?>/family-members"><div class="modal-body"><?= AppHelpersCsrf::field() ?><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" required></div><div class="col-md-6"><label class="form-label">Relation</label><input name="relation" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control"></div><div class="col-md-6"><label class="form-label">Age (if DOB unknown)</label><input type="number" name="age" min="0" max="130" class="form-control"></div>
<div class="col-12"><label class="form-label">Phone</label><input name="phone" class="form-control"></div></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Family Member</button></div></form>
</div></div></div>

<?php foreach ($familyMembers as $fm): ?>
<div class="modal fade app-form-modal" id="editFamilyModal<?= (int) $fm['id'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Family</div><h5 class="modal-title">Edit Family Member</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/members/<?= (int) $member['id'] ?>/family-members"><div class="modal-body"><?= AppHelpersCsrf::field() ?><input type="hidden" name="id" value="<?= (int) $fm['id'] ?>"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name *</label><input name="name" class="form-control" value="<?= htmlspecialchars($fm['name']) ?>" required></div><div class="col-md-6"><label class="form-label">Relation</label><input name="relation" class="form-control" value="<?= htmlspecialchars($fm['relation'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($fm['date_of_birth'] ?? '') ?>"></div><div class="col-md-6"><label class="form-label">Age</label><input type="number" name="age" min="0" max="130" class="form-control" value="<?= $fm['date_of_birth'] ? '' : (int) ($fm['age'] ?? 0) ?>"></div>
<div class="col-12"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= htmlspecialchars($fm['phone'] ?? '') ?>"></div></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div></form>
</div></div></div>
<?php endforeach; ?>

<!-- Emergency add/edit -->
<div class="modal fade app-form-modal" id="addEmergencyModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Emergency Contact</div><h5 class="modal-title">Add Emergency Contact</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/members/<?= (int) $member['id'] ?>/emergency-contacts"><div class="modal-body"><?= AppHelpersCsrf::field() ?><div class="mb-3"><label class="form-label">Name *</label><input name="name" class="form-control" required></div><div class="row g-3"><div class="col-md-6"><label class="form-label">Relation</label><input name="relation" class="form-control"></div><div class="col-md-6"><label class="form-label">Phone *</label><input name="phone" class="form-control" required></div></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Contact</button></div></form>
</div></div></div>
<?php foreach ($emergencyContacts as $ec): ?>
<div class="modal fade app-form-modal" id="editEmergencyModal<?= (int) $ec['id'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Emergency Contact</div><h5 class="modal-title">Edit Emergency Contact</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/members/<?= (int) $member['id'] ?>/emergency-contacts"><div class="modal-body"><?= AppHelpersCsrf::field() ?><input type="hidden" name="id" value="<?= (int) $ec['id'] ?>"><div class="mb-3"><label class="form-label">Name *</label><input name="name" class="form-control" value="<?= htmlspecialchars($ec['name']) ?>" required></div><div class="row g-3"><div class="col-md-6"><label class="form-label">Relation</label><input name="relation" class="form-control" value="<?= htmlspecialchars($ec['relation'] ?? '') ?>"></div><div class="col-md-6"><label class="form-label">Phone *</label><input name="phone" class="form-control" value="<?= htmlspecialchars($ec['phone']) ?>" required></div></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div></form>
</div></div></div>
<?php endforeach; ?>

<!-- Vehicle edit -->
<?php foreach ($vehicles as $vehicle): ?>
<div class="modal fade app-form-modal" id="editVehicleModal<?= (int) $vehicle['id'] ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Vehicle</div><h5 class="modal-title">Edit Vehicle</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/vehicles/<?= (int) $vehicle['id'] ?>"><div class="modal-body"><?= AppHelpersCsrf::field() ?><div class="row g-3">
<div class="col-md-6"><label class="form-label">Type *</label><select name="vehicle_type" class="form-select"><option value="four_wheeler" <?= $vehicle['vehicle_type']==='four_wheeler'?'selected':'' ?>>4-Wheeler</option><option value="two_wheeler" <?= $vehicle['vehicle_type']==='two_wheeler'?'selected':'' ?>>2-Wheeler</option></select></div>
<div class="col-md-6"><label class="form-label">Registration *</label><input name="registration_number" class="form-control" value="<?= htmlspecialchars($vehicle['registration_number']) ?>" required></div>
<div class="col-md-4"><label class="form-label">Make</label><input name="make" class="form-control" value="<?= htmlspecialchars($vehicle['make'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Model</label><input name="model" class="form-control" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Color</label><input name="color" class="form-control" value="<?= htmlspecialchars($vehicle['color'] ?? '') ?>"></div>
</div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div></form>
</div></div></div>
<?php endforeach; ?>

<!-- Document upload -->
<div class="modal fade app-form-modal" id="uploadDocumentModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Documents</div><h5 class="modal-title">Upload Document</h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="/members/<?= (int) $member['id'] ?>/documents" enctype="multipart/form-data"><div class="modal-body"><?= AppHelpersCsrf::field() ?><p class="resident-form-note mb-4">Upload a resident document such as Aadhaar, PAN, address proof, or another approved society record.</p><div class="mb-3"><label class="form-label">Document Title *</label><input name="title" class="form-control" placeholder="e.g. Aadhaar Card" required></div><div><label class="form-label">File *</label><input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="form-control" required></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>Upload Document</button></div></form>
</div></div></div>

<?php if ($member['member_type'] === 'tenant' && !empty($ownerCandidates)): ?>
<div class="modal fade app-form-modal" id="leaseModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
<div class="modal-header"><div><div class="resident-eyebrow">Tenant Management</div><h5 class="modal-title"><?= $tenant ? 'Edit Lease Details' : 'Set Up Lease' ?></h5></div><button class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="post" action="<?= $tenant ? '/leases/' . (int) $tenant['id'] : '/members/' . (int) $member['id'] . '/lease' ?>" enctype="multipart/form-data"><div class="modal-body"><?= AppHelpersCsrf::field() ?>
<div class="row g-3"><div class="col-12"><label class="form-label">Flat Owner *</label><select name="owner_member_id" class="form-select" required><?php foreach ($ownerCandidates as $oc): ?><option value="<?= (int) $oc['id'] ?>" <?= $tenant && (int)$oc['id']===(int)$tenant['owner_member_id']?'selected':'' ?>><?= htmlspecialchars($oc['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Lease Start</label><input type="date" name="lease_start" class="form-control" value="<?= htmlspecialchars($tenant['lease_start'] ?? '') ?>"></div><div class="col-md-6"><label class="form-label">Lease End</label><input type="date" name="lease_end" class="form-control" value="<?= htmlspecialchars($tenant['lease_end'] ?? '') ?>"></div>
<div class="col-12"><label class="form-label">Agreement Document</label><?php if ($tenant && $tenant['agreement_doc_path']): ?><a href="/leases/<?= (int) $tenant['id'] ?>/agreement" target="_blank" class="d-block mb-2">View current agreement</a><?php endif; ?><input type="file" name="agreement_doc" accept=".jpg,.jpeg,.png,.pdf" class="form-control"></div></div>
</div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Lease Details</button></div></form>
</div></div></div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
