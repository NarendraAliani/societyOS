<?php
$pageTitle='My Payment Proofs';
ob_start();
?>
<p><a href="/resident/bills">&laquo; Back to My Bills</a></p>
<div class="card border-0 shadow-sm"><div class="card-body">
<h5>My Payment Proofs</h5>
<p class="text-muted small">Submitted proofs are reviewed by your society. A payment is recorded only after approval.</p>
<div class="table-responsive"><table class="table table-hover align-middle">
<thead><tr><th>Bill</th><th>Submitted</th><th>Amount</th><th>Platform</th><th>Transaction ID</th><th>Status</th><th>Review note</th></tr></thead><tbody>
<?php foreach ($proofs as $proof): ?>
<tr><td><?= htmlspecialchars($proof['bill_number'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($proof['created_at'],ENT_QUOTES,'UTF-8') ?></td><td>₹<?= number_format((float)$proof['amount'],2) ?></td><td><?= htmlspecialchars($proof['platform'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($proof['transaction_id'],ENT_QUOTES,'UTF-8') ?></td>
<td><span class="badge bg-<?= $proof['status']==='approved'?'success':($proof['status']==='rejected'?'danger':'warning') ?>"><?= ucfirst($proof['status']) ?></span></td>
<td><?= htmlspecialchars((string)($proof['review_notes']??'—'),ENT_QUOTES,'UTF-8') ?><?php if (!empty($proof['screenshot_path'])): ?> <a href="/billing/payment-proofs/<?= (int)$proof['id'] ?>/file" target="_blank" rel="noopener">View proof</a><?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$proofs): ?><tr><td colspan="7" class="text-center text-muted py-4">No payment proofs submitted yet.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php';
