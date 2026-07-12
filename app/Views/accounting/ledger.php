<?php
$pageTitle = 'Ledger';
ob_start();
?>
<p><a href="/accounting/accounts">&laquo; Back to Accounts</a></p>

<form method="get" action="/accounting/ledger" class="mb-3" style="max-width: 280px;">
    <select name="account_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">All accounts</option>
        <?php foreach ($accounts as $account): ?>
            <option value="<?= (int) $account['id'] ?>" <?= (isset($_GET['account_id']) && (int) $_GET['account_id'] === (int) $account['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($account['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Date</th><th>Account</th><th>Type</th><th>Reference</th><th>Narration</th><th class="text-end">Amount</th></tr></thead>
            <tbody>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['entry_date']) ?></td>
                    <td><?= htmlspecialchars($entry['account_name']) ?></td>
                    <td><span class="badge bg-<?= $entry['entry_type'] === 'credit' ? 'success' : 'danger' ?>"><?= ucfirst($entry['entry_type']) ?></span></td>
                    <td><?= htmlspecialchars(ucfirst($entry['reference_type'])) ?> #<?= (int) $entry['reference_id'] ?></td>
                    <td><?= htmlspecialchars($entry['narration'] ?? '') ?></td>
                    <td class="text-end fw-bold <?= $entry['entry_type'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                        <?= $entry['entry_type'] === 'credit' ? '+' : '-' ?><?= number_format((float) $entry['amount'], 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($entries)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No ledger entries yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
