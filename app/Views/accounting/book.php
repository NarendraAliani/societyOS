<?php
ob_start();
?>
<p><a href="/accounting/accounts">&laquo; Back to Accounts</a></p>

<form method="get" action="<?= htmlspecialchars($basePath) ?>" class="row g-2 mb-3" style="max-width: 640px;">
    <div class="col-4">
        <select name="account_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($accounts as $acc): ?>
                <option value="<?= (int) $acc['id'] ?>" <?= $accountId !== null && (int) $accountId === (int) $acc['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($acc['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-3"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>"></div>
    <div class="col-3"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>"></div>
    <div class="col-2"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>

<?php if (empty($accounts)): ?>
    <div class="alert alert-secondary">No <?= htmlspecialchars($accountType) ?> accounts set up yet. <a href="/accounting/accounts">Add one</a>.</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6><?= htmlspecialchars($account['name'] ?? '') ?> <small class="text-muted"><?= htmlspecialchars($from) ?> &rarr; <?= htmlspecialchars($to) ?></small></h6>
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Reference</th><th>Narration</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr></thead>
            <tbody>
                <tr class="table-light">
                    <td colspan="5" class="fw-bold">Opening Balance</td>
                    <td class="text-end fw-bold"><?= number_format($openingBalance, 2) ?></td>
                </tr>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['entry_date']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($entry['reference_type'])) ?> #<?= (int) $entry['reference_id'] ?></td>
                        <td><?= htmlspecialchars($entry['narration'] ?? '') ?></td>
                        <td class="text-end text-danger"><?= $entry['entry_type'] === 'debit' ? number_format((float) $entry['amount'], 2) : '' ?></td>
                        <td class="text-end text-success"><?= $entry['entry_type'] === 'credit' ? number_format((float) $entry['amount'], 2) : '' ?></td>
                        <td class="text-end"><?= number_format((float) $entry['running_balance'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No entries in this date range.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td colspan="5" class="fw-bold">Closing Balance</td>
                    <td class="text-end fw-bold"><?= number_format($closingBalance, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
