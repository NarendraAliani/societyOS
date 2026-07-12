<?php
$pageTitle = 'Bill ' . $bill['bill_number'];
ob_start();
$outstanding = (float) $bill['total_amount'] - (float) $bill['paid_amount'];
?>
<p><a href="/billing">&laquo; Back to Bills</a></p>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6><?= htmlspecialchars($bill['bill_number']) ?></h6>
                <p class="mb-1"><strong>Flat:</strong> <?= htmlspecialchars($bill['wing_name'] . '-' . $bill['flat_number']) ?></p>
                <p class="mb-1"><strong>Period:</strong> <?= htmlspecialchars($bill['bill_period_start']) ?> &rarr; <?= htmlspecialchars($bill['bill_period_end']) ?></p>
                <p class="mb-1"><strong>Due Date:</strong> <?= htmlspecialchars($bill['due_date']) ?></p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $bill['status'])) ?></span></p>
                <table class="table table-sm mt-3">
                    <thead><tr><th>Charge</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr><td><?= htmlspecialchars($item['description'] ?? '') ?></td><td class="text-end"><?= number_format((float) $item['amount'], 2) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold"><td>Total</td><td class="text-end"><?= number_format((float) $bill['total_amount'], 2) ?></td></tr>
                        <tr><td>Paid</td><td class="text-end"><?= number_format((float) $bill['paid_amount'], 2) ?></td></tr>
                        <tr class="fw-bold text-danger"><td>Outstanding</td><td class="text-end"><?= number_format($outstanding, 2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if ($outstanding > 0): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Record Payment</h6>
                <form method="post" action="/billing/<?= (int) $bill['id'] ?>/payments">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" max="<?= $outstanding ?>" value="<?= $outstanding ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mode</label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">Record Payment</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Payment History</h6>
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Mode</th><th class="text-end">Amount</th><th>Receipt</th></tr></thead>
                    <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['paid_at']) ?></td>
                            <td><?= htmlspecialchars(str_replace('_', ' ', $payment['payment_mode'])) ?></td>
                            <td class="text-end"><?= number_format((float) $payment['amount'], 2) ?></td>
                            <td><?php if ($payment['receipt_number']): ?><a href="/billing/payments/<?= (int) $payment['id'] ?>/receipt" target="_blank"><?= htmlspecialchars($payment['receipt_number']) ?></a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
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
