<?php
$pageTitle = 'Parking Slot ' . $slot['slot_number'];
ob_start();
?>
<p><a href="/vehicles/parking">&laquo; Back to Parking</a></p>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Slot <?= htmlspecialchars($slot['slot_number']) ?>
                    <span class="badge bg-<?= $slot['is_allocated'] ? 'success' : 'secondary' ?>"><?= $slot['is_allocated'] ? 'Occupied' : 'Free' ?></span>
                </h6>
                <p class="text-muted small mb-0">
                    <?= $slot['slot_type'] === 'two_wheeler' ? '2-Wheeler' : '4-Wheeler' ?> rate:
                    <?= $slot['current_rate'] !== null ? number_format((float) $slot['current_rate'], 2) . '/mo' : 'not priced yet' ?>
                </p>

                <?php if (!$slot['is_allocated']): ?>
                <form method="post" action="/vehicles/parking/<?= (int) $slot['id'] ?>/allocate" class="mt-3">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Flat *</label>
                        <select name="flat_id" class="form-select" required>
                            <option value="">Select flat</option>
                            <?php foreach ($flats as $flat): ?>
                                <option value="<?= (int) $flat['id'] ?>"><?= htmlspecialchars($flat['wing_name'] . '-' . $flat['flat_number']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vehicle (optional)</label>
                        <select name="vehicle_id" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= (int) $vehicle['id'] ?>"><?= htmlspecialchars($vehicle['registration_number'] . ' (' . $vehicle['wing_name'] . '-' . $vehicle['flat_number'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Billing *</label>
                        <select name="billing_status" class="form-select" required>
                            <option value="paid">Paid Parking &mdash; billed at the type's rate</option>
                            <option value="free">Free Allocation &mdash; never billed, regardless of rate</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allocated From</label>
                        <input type="date" name="allocated_from" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Allocate Slot</button>
                </form>
                <?php else: ?>
                    <?php $active = array_filter($history, fn ($h) => $h['allocated_to'] === null); $active = reset($active); ?>
                    <?php if ($active): ?>
                    <p class="mt-3">Currently allocated to <strong><?= htmlspecialchars($active['wing_name'] . '-' . $active['flat_number']) ?></strong>
                        <?php if ($active['registration_number']): ?> &middot; <?= htmlspecialchars($active['registration_number']) ?><?php endif; ?>
                        since <?= htmlspecialchars($active['allocated_from']) ?>
                        &mdash; <span class="badge bg-<?= $active['is_chargeable'] ? 'warning text-dark' : 'light text-dark border' ?>"><?= $active['is_chargeable'] ? 'Paid' : 'Free' ?></span>.</p>
                    <form method="post" action="/parking-allocations/<?= (int) $active['id'] ?>/release" onsubmit="return confirm('Release this slot?');">
                        <?= \App\Helpers\Csrf::field() ?>
                        <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger w-100">Release Slot</button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Allocation History</h6>
                <table class="table table-sm">
                    <thead><tr><th>Flat</th><th>Vehicle</th><th>From</th><th>To</th><th>Billing</th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) ?></td>
                            <td><?= htmlspecialchars($row['registration_number'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['allocated_from']) ?></td>
                            <td><?= htmlspecialchars($row['allocated_to'] ?? 'Active') ?></td>
                            <td><span class="badge bg-<?= $row['is_chargeable'] ? 'warning text-dark' : 'light text-dark border' ?>"><?= $row['is_chargeable'] ? 'Paid' : 'Free' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($history)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No allocations yet.</td></tr>
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
