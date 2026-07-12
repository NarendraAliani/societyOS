<?php
$pageTitle = 'Parking';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="mb-0"><a href="/vehicles">&laquo; Back to Vehicles</a></p>
    <a href="/vehicles/parking/rates" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-tags me-1"></i>Manage Rates</a>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Slot</th><th>Type</th><th>Rate</th><th>Status</th><th>Allocated To</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($slots as $slot): ?>
                        <tr>
                            <td><a href="/vehicles/parking/<?= (int) $slot['id'] ?>"><?= htmlspecialchars($slot['slot_number']) ?></a></td>
                            <td><span class="badge bg-<?= $slot['slot_type'] === 'two_wheeler' ? 'info' : 'primary' ?>"><?= $slot['slot_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?></span></td>
                            <td><?= $slot['current_rate'] !== null ? number_format((float) $slot['current_rate'], 2) . '/mo' : '<span class="text-muted">Not priced</span>' ?></td>
                            <td><span class="badge bg-<?= $slot['is_allocated'] ? 'success' : 'secondary' ?>"><?= $slot['is_allocated'] ? 'Occupied' : 'Free' ?></span></td>
                            <td>
                                <?php if ($slot['flat_id']): ?>
                                    <?= htmlspecialchars($slot['wing_name'] . '-' . $slot['flat_number']) ?>
                                    <?php if ($slot['registration_number']): ?> &middot; <?= htmlspecialchars($slot['registration_number']) ?><?php endif; ?>
                                    <span class="badge bg-<?= $slot['is_chargeable'] ? 'warning text-dark' : 'light text-dark border' ?> ms-1"><?= $slot['is_chargeable'] ? 'Paid' : 'Free' ?></span>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-slot-<?= (int) $slot['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/vehicles/parking/<?= (int) $slot['id'] ?>/delete" onsubmit="return confirm('Delete this slot?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-slot-<?= (int) $slot['id'] ?>">
                            <td colspan="6">
                                <form method="post" action="/vehicles/parking/<?= (int) $slot['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-4">
                                        <label class="form-label small">Slot Number</label>
                                        <input type="text" name="slot_number" class="form-control form-control-sm" value="<?= htmlspecialchars($slot['slot_number']) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Type</label>
                                        <select name="slot_type" class="form-select form-select-sm">
                                            <option value="four_wheeler" <?= $slot['slot_type'] === 'four_wheeler' ? 'selected' : '' ?>>4-Wheeler</option>
                                            <option value="two_wheeler" <?= $slot['slot_type'] === 'two_wheeler' ? 'selected' : '' ?>>2-Wheeler</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($slots)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No parking slots yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Parking Slot</h6>
                <form method="post" action="/vehicles/parking">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Slot Number</label>
                        <input type="text" name="slot_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="slot_type" class="form-select">
                            <option value="four_wheeler">4-Wheeler</option>
                            <option value="two_wheeler">2-Wheeler</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Slot</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
