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
