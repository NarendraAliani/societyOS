<?php
$pageTitle = 'Delivery Register';
ob_start();
?>
<p><a href="/visitors">&laquo; Back to Visitor Register</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Received</th><th>Flat</th><th>Courier</th><th>Recipient</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($deliveries as $delivery): ?>
                        <tr>
                            <td><?= htmlspecialchars($delivery['received_at']) ?></td>
                            <td><?= htmlspecialchars($delivery['wing_name'] . '-' . $delivery['flat_number']) ?></td>
                            <td><?= htmlspecialchars($delivery['courier_company'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($delivery['recipient_name'] ?? '-') ?></td>
                            <td>
                                <?php if ($delivery['collected_at']): ?>
                                    <span class="badge bg-success">Collected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">At gate</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!$delivery['collected_at']): ?>
                                    <form method="post" action="/visitors/deliveries/<?= (int) $delivery['id'] ?>/collect">
                                        <?= \App\Helpers\Csrf::field() ?>
                                        <button class="btn btn-sm btn-outline-success">Mark Collected</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($deliveries)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No deliveries logged yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Log Delivery</h6>
                <form method="post" action="/visitors/deliveries">
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
                        <label class="form-label">Courier Company</label>
                        <input type="text" name="courier_company" class="form-control" placeholder="e.g. Amazon, Bluedart">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recipient Name</label>
                        <input type="text" name="recipient_name" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log Delivery</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
