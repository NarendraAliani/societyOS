<?php
$pageTitle = 'Visitor Register';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Visitor Register</h5>
    <div>
        <a href="/visitors/passes" class="btn btn-outline-secondary btn-sm me-2"><i class="fa-solid fa-qrcode me-1"></i>Passes</a>
        <a href="/visitors/deliveries" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-box me-1"></i>Deliveries</a>
    </div>
</div>

<form method="get" action="/visitors" class="mb-3" style="max-width: 220px;">
    <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d')) ?>" onchange="this.form.submit()">
</form>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Time</th><th>Visitor</th><th>Flat</th><th>Purpose</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($visitors as $visitor): ?>
                        <tr>
                            <td><?= htmlspecialchars($visitor['check_in_at']) ?></td>
                            <td><?= htmlspecialchars($visitor['name']) ?><?php if ($visitor['phone']): ?><br><small class="text-muted"><?= htmlspecialchars($visitor['phone']) ?></small><?php endif; ?></td>
                            <td><?= htmlspecialchars($visitor['wing_name'] . '-' . $visitor['flat_number']) ?></td>
                            <td><?= htmlspecialchars($visitor['purpose'] ?? '-') ?></td>
                            <td>
                                <?php $badge = match ($visitor['approval_status']) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' }; ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($visitor['approval_status']) ?></span>
                                <?php if ($visitor['check_out_at']): ?><br><span class="badge bg-secondary mt-1">Checked out</span><?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($visitor['approval_status'] === 'pending'): ?>
                                    <form method="post" action="/visitors/<?= (int) $visitor['id'] ?>/approve" class="d-inline">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-success" title="Approve"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <form method="post" action="/visitors/<?= (int) $visitor['id'] ?>/reject" class="d-inline">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                <?php elseif ($visitor['approval_status'] === 'approved' && !$visitor['check_out_at']): ?>
                                    <form method="post" action="/visitors/<?= (int) $visitor['id'] ?>/checkout" class="d-inline">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-outline-secondary">Check Out</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($visitors)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No visitors logged for this date.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Log Visitor</h6>
                <form method="post" action="/visitors">
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
                        <label class="form-label">Visitor Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Guest, Plumber">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log Visitor</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
