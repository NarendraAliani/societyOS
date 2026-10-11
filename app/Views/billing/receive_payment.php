<?php
$pageTitle = 'Receive Payment';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h5 class="mb-1">Receive Payment</h5>
        <p class="text-muted mb-0">Record a payment received at the society office and issue its receipt.</p>
    </div>
    <a href="/billing" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Maintenance Bills</a>
</div>

<?php if (empty($bills)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
        <i class="fa-solid fa-circle-check text-success fs-2 mb-3"></i>
        <h5>No outstanding bills</h5>
        <p class="text-muted mb-0">All maintenance bills for this society are fully paid.</p>
    </div></div>
<?php else: ?>
<div class="row g-3">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="post" action="/billing/receive-payment" id="receive-payment-form">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="bill_id">Resident / Flat / Bill <span class="text-danger">*</span></label>
                        <select class="form-select" name="bill_id" id="bill_id" required>
                            <option value="">Select outstanding bill</option>
                            <?php foreach ($bills as $openBill): ?>
                                <option value="<?= (int) $openBill['id'] ?>" data-outstanding="<?= htmlspecialchars(number_format((float) $openBill['outstanding'], 2, '.', '')) ?>">
                                    <?= htmlspecialchars(($openBill['resident_name'] ?: 'Resident not linked') . ' · ' . $openBill['wing_name'] . '-' . $openBill['flat_number'] . ' · ' . $openBill['bill_number'] . ' · Due ₹' . number_format((float) $openBill['outstanding'], 2)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Only bills with a remaining balance are listed.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount received (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" required>
                            <div class="form-text" id="outstanding-help">Select a bill to see its outstanding balance.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="payment_mode">Payment mode <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_mode" id="payment_mode" required>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="reference_number">Reference / Transaction No.</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number" maxlength="150" placeholder="Optional for cash; recommended for digital payments">
                        </div>
                    </div>
                    <div class="alert alert-info mt-4 mb-3"><i class="fa-solid fa-receipt me-2"></i>A receipt number is generated automatically after the payment is saved. You can open or print the receipt from the bill's payment history.</div>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>Receive Payment &amp; Generate Receipt</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6><i class="fa-solid fa-circle-info me-2"></i>Before confirming</h6>
            <ul class="small text-muted ps-3 mb-0">
                <li class="mb-2">Confirm the resident, flat, and bill with the payer.</li>
                <li class="mb-2">Enter the actual amount received; partial payments are allowed.</li>
                <li class="mb-2">The amount cannot exceed the outstanding bill balance.</li>
                <li>After saving, open the generated receipt from Payment History to print or share it.</li>
            </ul>
        </div></div>
    </div>
</div>
<script>
(function () {
    const billSelect = document.getElementById('bill_id');
    const amountInput = document.getElementById('amount');
    const help = document.getElementById('outstanding-help');
    function updateOutstanding() {
        const option = billSelect.options[billSelect.selectedIndex];
        const outstanding = option ? option.getAttribute('data-outstanding') : null;
        if (!outstanding) {
            amountInput.removeAttribute('max');
            amountInput.value = '';
            help.textContent = 'Select a bill to see its outstanding balance.';
            return;
        }
        amountInput.max = outstanding;
        amountInput.value = outstanding;
        help.textContent = 'Outstanding balance: ₹' + Number(outstanding).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    billSelect.addEventListener('change', updateOutstanding);
    updateOutstanding();
})();
</script>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
