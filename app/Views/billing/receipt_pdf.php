<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    .header { text-align: center; margin-bottom: 16px; }
    .header h2 { margin: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    td, th { padding: 6px 4px; border-bottom: 1px solid #ddd; text-align: left; }
    .right { text-align: right; }
    .total { font-weight: bold; font-size: 14px; }
    .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; }
</style>
</head>
<body>
    <div class="header">
        <h2><?= htmlspecialchars($society['name'] ?? 'SocietyOS') ?></h2>
        <p><?= htmlspecialchars($society['address'] ?? '') ?></p>
        <h3>Payment Receipt</h3>
    </div>

    <table>
        <tr><td>Receipt No.</td><td class="right"><?= htmlspecialchars($detail['receipt_number']) ?></td></tr>
        <tr><td>Bill No.</td><td class="right"><?= htmlspecialchars($detail['bill_number']) ?></td></tr>
        <tr><td>Flat</td><td class="right"><?= htmlspecialchars($detail['wing_name'] . '-' . $detail['flat_number']) ?></td></tr>
        <tr><td>Payment Date</td><td class="right"><?= htmlspecialchars($detail['paid_at']) ?></td></tr>
        <tr><td>Payment Mode</td><td class="right"><?= htmlspecialchars(str_replace('_', ' ', $detail['payment_mode'])) ?></td></tr>
        <?php if (!empty($detail['reference_number'])): ?>
        <tr><td>Reference No.</td><td class="right"><?= htmlspecialchars($detail['reference_number']) ?></td></tr>
        <?php endif; ?>
        <tr class="total"><td>Amount Paid</td><td class="right"><?= number_format((float) $detail['amount'], 2) ?></td></tr>
    </table>

    <div class="footer">
        This is a system-generated receipt from SocietyOS.
    </div>
</body>
</html>
