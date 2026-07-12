<?php
$pageTitle = 'Leave Requests';
ob_start();
?>
<p><a href="/staff">&laquo; Back to Staff</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Staff</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($leaveRequests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['staff_name']) ?></td>
                            <td><?= htmlspecialchars($request['from_date']) ?></td>
                            <td><?= htmlspecialchars($request['to_date']) ?></td>
                            <td><?= htmlspecialchars($request['reason'] ?? '-') ?></td>
                            <td>
                                <?php $badge = match ($request['status']) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' }; ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($request['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form method="post" action="/staff/leave/<?= (int) $request['id'] ?>" class="d-inline">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <form method="post" action="/staff/leave/<?= (int) $request['id'] ?>" class="d-inline">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leaveRequests)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No leave requests yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Log Leave Request</h6>
                <form method="post" action="/staff/leave">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Staff *</label>
                        <select name="staff_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($staff as $member): ?>
                                <option value="<?= (int) $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From *</label>
                        <input type="date" name="from_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">To *</label>
                        <input type="date" name="to_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="reason" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
