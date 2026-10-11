<?php
ob_start();
?>
<p><a href="/resident/bills">&laquo; Back to My Bills</a></p>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-4">
        <h4 class="mb-2">UPI QR Payment</h4>
        <?php if (!empty($upiUnavailableMessage)): ?>
            <div class="alert alert-warning text-start mx-auto" style="max-width: 680px" role="status">
                <?= htmlspecialchars($upiUnavailableMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <a class="btn btn-primary" href="/resident/bills">Return to My Bills</a>
        <?php else: ?>
            <p class="text-muted mb-1">Bill <?= htmlspecialchars((string) $bill['bill_number'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="fs-4 fw-semibold mb-3">₹<?= number_format($amount, 2) ?></p>
            <div class="d-inline-block bg-white border rounded-3 p-3 mb-3" style="max-width: 100%">
                <?= $qrSvg ?>
            </div>
            <p class="text-muted mx-auto" style="max-width: 520px">
                Scan this QR code using a UPI app and confirm the payee and amount before completing payment.
            </p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <button class="btn btn-outline-secondary" type="button" onclick="window.print()">Print QR</button>
                <a class="btn btn-primary" href="/resident/bills">Back to My Bills</a>
            </div>
            <hr class="my-4">
            <div class="mx-auto text-start" style="max-width:680px">
                <h5>Already paid? Submit payment proof</h5>
                <p class="text-muted small">Enter the transaction/reference ID from your UPI app or bank and optionally attach a screenshot. The bill will remain outstanding until the society verifies the payment.</p>
                <form method="post" action="/resident/payment-proofs" enctype="multipart/form-data">
                    <?= \App\Helpers\Csrf::field() ?>
                    <input type="hidden" name="bill_id" value="<?= (int)$bill['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="proof-amount">Amount paid (₹)</label><input class="form-control" id="proof-amount" name="amount" type="number" min="0.01" max="<?= htmlspecialchars(number_format((float)$amount, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>" step="0.01" value="<?= htmlspecialchars(number_format((float)$amount, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>" required><div class="form-text">Enter the amount you actually transferred. Maximum outstanding amount: ₹<?= number_format((float)$amount, 2) ?>.</div></div>
                        <div class="col-md-6"><label class="form-label" for="proof-platform">Payment platform</label><select class="form-select" id="proof-platform" name="platform" required><option value="">Select platform</option><option>UPI app</option><option>Bank transfer</option><option>Debit/Credit card</option><option>Other</option></select></div>
                        <div class="col-md-6"><label class="form-label" for="proof-txn">Transaction / UTR / reference ID</label><input class="form-control" id="proof-txn" name="transaction_id" maxlength="100" required placeholder="Enter exact transaction reference"></div>
                        <div class="col-12"><label class="form-label" for="proof-shot">Payment screenshot (optional)</label><input class="form-control" id="proof-shot" type="file" name="screenshot" accept="image/png,image/jpeg"><div class="form-text">PNG or JPG only. Do not upload screenshots containing PINs or passwords.</div></div>
                        <div class="col-12"><label class="form-label" for="proof-notes">Additional details (optional)</label><textarea class="form-control" id="proof-notes" name="notes" maxlength="1000" rows="2" placeholder="Payment date, amount, or other context"></textarea></div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Submit Proof for Verification</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
@media print {
    #app-sidebar, .app-topbar, #app-content > p, #app-content .btn { display: none !important; }
    #app-content { padding: 0 !important; }
    #app-content .card { box-shadow: none !important; border: 0 !important; }
}
</style>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
