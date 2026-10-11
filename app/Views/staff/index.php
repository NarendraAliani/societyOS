<?php
$pageTitle = 'Staff';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Staff</h5>
    <div>
        <a href="/staff/attendance" class="btn btn-outline-secondary btn-sm me-2">Attendance</a>
        <a href="/staff/leave" class="btn btn-outline-secondary btn-sm me-2">Leave</a>
        <a href="/staff/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add Staff</a>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th></th><th>Name</th><th>Designation</th><th>Phone</th><th>Joined</th><th>Status</th><th>Police Verification</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($staff as $member): ?>
                <?php
                $verificationBadge = match ($member['police_verification_status']) {
                    'verified' => 'success',
                    'not_verified' => 'danger',
                    default => 'warning text-dark',
                };
                $verificationLabel = match ($member['police_verification_status']) {
                    'verified' => 'Verified',
                    'not_verified' => 'Not Verified',
                    default => 'Pending',
                };
                ?>
                <tr>
                    <td>
                        <?php if ($member['photo_path']): ?>
                            <img src="/staff/<?= (int) $member['id'] ?>/file/photo" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" style="width:36px;height:36px;"><i class="fa-solid fa-user"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><a href="/staff/<?= (int) $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></a></td>
                    <td><?= htmlspecialchars($member['designation'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($member['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($member['joining_date'] ?? '-') ?></td>
                    <td>
                        <form method="post" action="/staff/<?= (int) $member['id'] ?>/toggle-status" class="d-inline">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button class="btn btn-sm <?= $member['status'] === 'active' ? 'btn-success' : 'btn-outline-secondary' ?>"><?= ucfirst($member['status']) ?></button>
                        </form>
                    </td>
                    <td><span class="badge bg-<?= $verificationBadge ?>"><?= $verificationLabel ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-staff-<?= (int) $member['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                        <form method="post" action="/staff/<?= (int) $member['id'] ?>/delete" onsubmit="return confirm('Remove this staff member?');" class="d-inline">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <tr class="collapse" id="edit-staff-<?= (int) $member['id'] ?>">
                    <td colspan="8">
                        <form method="post" action="/staff/<?= (int) $member['id'] ?>" enctype="multipart/form-data" class="row g-2 align-items-end p-2">
                            <?= \App\Helpers\Csrf::field() ?>
                            <div class="col-md-2">
                                <label class="form-label small">Name</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($member['name']) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Designation</label>
                                <input type="text" name="designation" class="form-control form-control-sm" value="<?= htmlspecialchars($member['designation'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Phone</label>
                                <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control form-control-sm" value="<?= htmlspecialchars($member['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Joining Date</label>
                                <input type="date" name="joining_date" class="form-control form-control-sm" value="<?= htmlspecialchars($member['joining_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Address</label>
                                <input type="text" name="address" class="form-control form-control-sm" value="<?= htmlspecialchars($member['address'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Photo, ID proof, and police verification are managed on this staff member's <a href="/staff/<?= (int) $member['id'] ?>">detail page</a>.</small>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($staff)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No staff added yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
