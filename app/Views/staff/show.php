<?php
$pageTitle = $staff['name'];
ob_start();
$verificationBadge = match ($staff['police_verification_status']) {
    'verified' => 'success',
    'not_verified' => 'danger',
    default => 'warning text-dark',
};
$verificationLabel = match ($staff['police_verification_status']) {
    'verified' => 'Verified',
    'not_verified' => 'Not Verified',
    default => 'Pending',
};
?>
<p><a href="/staff">&laquo; Back to Staff</a></p>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($staff['photo_path']): ?>
                            <img src="/staff/<?= (int) $staff['id'] ?>/file/photo" alt="Photo" class="rounded" style="width:64px;height:64px;object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width:64px;height:64px;"><i class="fa-solid fa-user fa-lg"></i></div>
                        <?php endif; ?>
                        <h6 class="mb-0"><?= htmlspecialchars($staff['name']) ?></h6>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editStaffModal" title="Edit Staff"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="modal" data-bs-target="#policeVerificationModal" title="Police Verification"><i class="fa-solid fa-shield-halved"></i></button>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#payrollEntryModal" title="Add Payroll Entry"><i class="fa-solid fa-money-check-dollar"></i></button>
                    </div>
                </div>
                <div class="mt-3 small">
                    <p class="mb-1"><strong>Designation:</strong> <?= htmlspecialchars($staff['designation'] ?? '-') ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($staff['phone'] ?? '-') ?></p>
                    <p class="mb-1"><strong>Date of Birth:</strong> <?= $staff['date_of_birth'] ? htmlspecialchars($staff['date_of_birth']) . ' (' . (int) $staff['display_age'] . ' yrs)' : '-' ?></p>
                    <p class="mb-1"><strong>Joined:</strong> <?= htmlspecialchars($staff['joining_date'] ?? '-') ?></p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= $staff['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($staff['status']) ?></span></p>
                    <p class="mb-0"><strong>ID Proof:</strong>
                        <?php if ($staff['id_proof_path']): ?>
                            <a href="/staff/<?= (int) $staff['id'] ?>/file/id_proof" target="_blank">View <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a>
                        <?php else: ?>
                            <span class="text-muted">Not uploaded</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Police Verification</h6>
                    <span class="badge bg-<?= $verificationBadge ?>"><?= $verificationLabel ?></span>
                </div>
                <?php if ($staff['police_verification_date']): ?>
                    <p class="text-muted small mt-2 mb-1">As of <?= htmlspecialchars($staff['police_verification_date']) ?></p>
                <?php endif; ?>
                <?php if ($staff['police_verification_doc_path']): ?>
                    <p class="mb-2"><a href="/staff/<?= (int) $staff['id'] ?>/file/police_doc" target="_blank">View Certificate <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a></p>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-warning mt-2" data-bs-toggle="modal" data-bs-target="#policeVerificationModal">
                    <i class="fa-solid fa-shield-halved me-1"></i>Update Verification
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6>Add Payroll Entry</h6>
                <p class="text-muted small mb-3">Record a salary/payroll entry for this staff member.</p>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#payrollEntryModal">
                    <i class="fa-solid fa-money-check-dollar me-1"></i>Add Payroll Entry
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Payroll History</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Period</th><th class="text-end">Basic</th><th class="text-end">Deductions</th><th class="text-end">Net</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($payroll as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['pay_period']) ?></td>
                                <td class="text-end"><?= number_format((float) $entry['basic_amount'], 2) ?></td>
                                <td class="text-end"><?= number_format((float) $entry['deductions'], 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format((float) $entry['net_amount'], 2) ?></td>
                                <td class="text-end">
                                    <?php if ($entry['paid_at']): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#markPayrollPaidModal<?= (int) $entry['id'] ?>">Mark Paid</button>
                                        <div class="modal fade app-form-modal" id="markPayrollPaidModal<?= (int) $entry['id'] ?>" tabindex="-1" aria-labelledby="markPayrollPaidLabel<?= (int) $entry['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="markPayrollPaidLabel<?= (int) $entry['id'] ?>">Mark Payroll as Paid</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="post" action="/staff/payroll/<?= (int) $entry['id'] ?>/mark-paid">
                                                        <div class="modal-body">
                                                            <?= \App\Helpers\Csrf::field() ?>
                                                            <input type="hidden" name="staff_id" value="<?= (int) $staff['id'] ?>">
                                                            <p class="mb-0">Mark the <strong><?= htmlspecialchars($entry['pay_period']) ?></strong> payroll entry for <strong><?= htmlspecialchars($staff['name']) ?></strong> as paid?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Mark Paid</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($payroll)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No payroll entries yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Staff -->
<div class="modal fade app-form-modal" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStaffModalLabel">Edit / Manage Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/staff/<?= (int) $staff['id'] ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($staff['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($staff['designation'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($staff['phone'] ?? '') ?>" inputmode="tel">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($staff['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="<?= htmlspecialchars($staff['joining_date'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter complete address"><?= htmlspecialchars($staff['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Replace Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                            <div class="form-text">JPG/PNG image.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Replace ID Proof</label>
                            <input type="file" name="id_proof" class="form-control" accept="image/jpeg,image/png,application/pdf">
                            <div class="form-text">JPG/PNG/PDF document.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Police Verification -->
<div class="modal fade app-form-modal" id="policeVerificationModal" tabindex="-1" aria-labelledby="policeVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="policeVerificationModalLabel">Police Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/staff/<?= (int) $staff['id'] ?>/police-verification" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Verification Status</label>
                            <select name="police_verification_status" class="form-select">
                                <option value="pending" <?= $staff['police_verification_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="verified" <?= $staff['police_verification_status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
                                <option value="not_verified" <?= $staff['police_verification_status'] === 'not_verified' ? 'selected' : '' ?>>Not Verified</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Verification Date</label>
                            <input type="date" name="police_verification_date" class="form-control" value="<?= htmlspecialchars($staff['police_verification_date'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Certificate <span class="text-muted">(optional)</span></label>
                            <input type="file" name="police_verification_doc" class="form-control" accept="image/jpeg,image/png,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payroll Entry -->
<div class="modal fade app-form-modal" id="payrollEntryModal" tabindex="-1" aria-labelledby="payrollEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payrollEntryModalLabel">Add Payroll Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/staff/<?= (int) $staff['id'] ?>/payroll">
                <div class="modal-body">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Pay Period <span class="text-danger">*</span></label>
                            <input type="month" name="pay_period" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Basic Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="basic_amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deductions</label>
                            <input type="number" step="0.01" min="0" name="deductions" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
