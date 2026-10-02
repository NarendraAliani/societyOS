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
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-staff"><i class="fa-solid fa-pen"></i></button>
                </div>
                <p class="mb-1 mt-3"><strong>Designation:</strong> <?= htmlspecialchars($staff['designation'] ?? '-') ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($staff['phone'] ?? '-') ?></p>
                <p class="mb-1"><strong>Date of Birth:</strong> <?= $staff['date_of_birth'] ? htmlspecialchars($staff['date_of_birth']) . ' (' . (int) $staff['display_age'] . ' yrs)' : '-' ?></p>
                <p class="mb-1"><strong>Joined:</strong> <?= htmlspecialchars($staff['joining_date'] ?? '-') ?></p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= $staff['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($staff['status']) ?></span></p>
                <p class="mb-1"><strong>ID Proof:</strong>
                    <?php if ($staff['id_proof_path']): ?>
                        <a href="/staff/<?= (int) $staff['id'] ?>/file/id_proof" target="_blank">View <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a>
                    <?php else: ?>
                        <span class="text-muted">Not uploaded</span>
                    <?php endif; ?>
                </p>

                <div class="collapse mt-3" id="edit-staff">
                    <form method="post" action="/staff/<?= (int) $staff['id'] ?>" enctype="multipart/form-data">
                        <?= \App\Helpers\Csrf::field() ?>
                        <div class="mb-2">
                            <label class="form-label small">Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['name']) ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Designation</label>
                            <input type="text" name="designation" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['designation'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['joining_date'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Address</label>
                            <input type="text" name="address" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['address'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Replace Photo</label>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/jpeg,image/png">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Replace ID Proof</label>
                            <input type="file" name="id_proof" class="form-control form-control-sm" accept="image/jpeg,image/png,application/pdf">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                    </form>
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
                <form method="post" action="/staff/<?= (int) $staff['id'] ?>/police-verification" enctype="multipart/form-data" class="mt-2">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Status</label>
                            <select name="police_verification_status" class="form-select form-select-sm">
                                <option value="pending" <?= $staff['police_verification_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="verified" <?= $staff['police_verification_status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
                                <option value="not_verified" <?= $staff['police_verification_status'] === 'not_verified' ? 'selected' : '' ?>>Not Verified</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Date</label>
                            <input type="date" name="police_verification_date" class="form-control form-control-sm" value="<?= htmlspecialchars($staff['police_verification_date'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Certificate (optional)</label>
                            <input type="file" name="police_verification_doc" class="form-control form-control-sm" accept="image/jpeg,image/png,application/pdf">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Update Verification</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6>Add Payroll Entry</h6>
                <form method="post" action="/staff/<?= (int) $staff['id'] ?>/payroll">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Pay Period *</label>
                        <input type="month" name="pay_period" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Basic Amount *</label>
                        <input type="number" step="0.01" name="basic_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deductions</label>
                        <input type="number" step="0.01" name="deductions" class="form-control" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Entry</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Payroll History</h6>
                <table class="table table-sm">
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
                                    <form method="post" action="/staff/payroll/<?= (int) $entry['id'] ?>/mark-paid">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="staff_id" value="<?= (int) $staff['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Mark Paid</button>
                                    </form>
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
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
