<?php
$pageTitle = $head['name'] . ' — Rates';
ob_start();
$today = date('Y-m-d');

// $rates is ordered DESC by effective_from — the "current" rate is the first one
// whose effective_from is not in the future.
$currentRateId = null;
foreach ($rates as $rate) {
    if ($rate['effective_from'] <= $today) {
        $currentRateId = $rate['id'];
        break;
    }
}
?>
<p><a href="/society/maintenance-heads">&laquo; Back to Maintenance Configuration</a></p>
<div class="row g-3">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Rate History — <?= htmlspecialchars($head['name']) ?></h6>
                <p class="text-muted small">Past and current rates are kept as permanent history and can't be edited or removed. Only a not-yet-effective (future-dated) scheduled change can be deleted.</p>
                <table class="table table-sm">
                    <thead><tr><th>Effective From</th><th class="text-end">Amount</th><th>Status</th><th>Set By</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($rates as $rate): ?>
                        <?php $isFuture = $rate['effective_from'] > $today; ?>
                        <tr>
                            <td><?= htmlspecialchars($rate['effective_from']) ?></td>
                            <td class="text-end fw-bold"><?= number_format((float) $rate['amount'], 2) ?></td>
                            <td>
                                <?php if ($isFuture): ?>
                                    <span class="badge bg-info">Scheduled</span>
                                <?php elseif ($rate['id'] === $currentRateId): ?>
                                    <span class="badge bg-success">Current</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Past</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($rate['created_by_name'] ?? '-') ?></td>
                            <td class="text-end">
                                <?php if ($isFuture): ?>
                                    <form method="post" action="/maintenance-head-rates/<?= (int) $rate['id'] ?>/delete" onsubmit="return confirm('Remove this scheduled rate change?');">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rates)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No rate history.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Schedule a Rate Change</h6>
                <form method="post" action="/society/maintenance-heads/<?= (int) $head['id'] ?>/rates">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">New Amount *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effective From *</label>
                        <input type="date" name="effective_from" class="form-control" value="<?= $today ?>" required>
                        <div class="form-text">Use today's date for an immediate change, or a future date to schedule it in advance.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Schedule Rate</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
