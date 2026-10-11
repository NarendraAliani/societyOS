<?php
$pageTitle='Payment Proof Verification';
ob_start();
?>
<p><a href="/billing">&laquo; Back to Billing</a></p>
<div class="card border-0 shadow-sm"><div class="card-body">
<h5>Pending Payment Proofs</h5>
<p class="text-muted small">Verify the transaction in the bank/UPI statement before approving. Approval records the payment against the outstanding bill and issues the normal receipt.</p>
<div class="table-responsive"><table class="table table-hover align-middle">
<thead><tr><th>Resident / Flat</th><th>Bill</th><th>Amount</th><th>Platform / Transaction</th><th>Submitted</th><th>Evidence</th><th style="min-width:320px">Review</th></tr></thead><tbody>
<?php foreach ($proofs as $proof): ?>
<tr><td><?= htmlspecialchars($proof['member_name'],ENT_QUOTES,'UTF-8') ?><div class="small text-muted"><?= htmlspecialchars($proof['wing_name'].'-'.$proof['flat_number'],ENT_QUOTES,'UTF-8') ?></div></td>
<td><a href="/billing/<?= (int)$proof['maintenance_bill_id'] ?>"><?= htmlspecialchars($proof['bill_number'],ENT_QUOTES,'UTF-8') ?></a><div class="small text-muted">Outstanding ₹<?= number_format(max(0,(float)$proof['total_amount']-(float)$proof['paid_amount']),2) ?></div></td>
<td>₹<?= number_format((float)$proof['amount'],2) ?></td><td><?= htmlspecialchars($proof['platform'],ENT_QUOTES,'UTF-8') ?><div class="small"><code><?= htmlspecialchars($proof['transaction_id'],ENT_QUOTES,'UTF-8') ?></code></div><?php if (!empty($proof['notes'])): ?><div class="small text-muted"><?= htmlspecialchars($proof['notes'],ENT_QUOTES,'UTF-8') ?></div><?php endif; ?></td>
<td><?= htmlspecialchars($proof['created_at'],ENT_QUOTES,'UTF-8') ?></td>
<td><?php if (!empty($proof['screenshot_path'])): ?><a href="/billing/payment-proofs/<?= (int)$proof['id'] ?>/file" target="_blank" rel="noopener">Open screenshot</a><?php else: ?><span class="text-muted">No screenshot</span><?php endif; ?></td>
<td><form method="post" action="/billing/payment-proofs/<?= (int)$proof['id'] ?>/review"><?= \App\Helpers\Csrf::field() ?><textarea class="form-control form-control-sm mb-2" name="review_notes" rows="2" maxlength="1000" placeholder="Optional approval note; required for rejection"></textarea><div class="d-flex gap-2"><button class="btn btn-sm btn-success" name="decision" value="approve" type="submit" onclick="return confirm('Verify the transaction and record this payment?')">Verify &amp; Approve</button><button class="btn btn-sm btn-outline-danger" name="decision" value="reject" type="submit" onclick="return confirm('Reject this payment proof? Add a reason above.')">Reject</button></div></form></td></tr>
<?php endforeach; ?>
<?php if (!$proofs): ?><tr><td colspan="7" class="text-center text-muted py-4">No pending payment proofs. You're all caught up.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php';
