<?php
$pageTitle = 'Accounts';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Accounts</h5>
    <div>
        <a href="/accounting/income" class="btn btn-outline-success btn-sm me-2">Income</a>
        <a href="/accounting/expenses" class="btn btn-outline-danger btn-sm me-2">Expenses</a>
        <a href="/accounting/vendors" class="btn btn-outline-secondary btn-sm me-2">Vendors</a>
        <a href="/accounting/ledger" class="btn btn-outline-primary btn-sm">Ledger</a>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Account</th><th>Type</th><th class="text-end">Opening</th><th class="text-end">Current Balance</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><a href="/accounting/ledger?account_id=<?= (int) $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></a></td>
                            <td><span class="badge bg-<?= $account['account_type'] === 'bank' ? 'info' : 'secondary' ?>"><?= ucfirst($account['account_type']) ?></span></td>
                            <td class="text-end"><?= number_format((float) $account['opening_balance'], 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format((float) $account['current_balance'], 2) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-account-<?= (int) $account['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-account-<?= (int) $account['id'] ?>">
                            <td colspan="5">
                                <form method="post" action="/accounting/accounts/<?= (int) $account['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-4">
                                        <label class="form-label small">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($account['name']) ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Type</label>
                                        <select name="account_type" class="form-select form-select-sm">
                                            <option value="cash" <?= $account['account_type'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                                            <option value="bank" <?= $account['account_type'] === 'bank' ? 'selected' : '' ?>>Bank</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Opening Balance</label>
                                        <input type="number" step="0.01" name="opening_balance" class="form-control form-control-sm" value="<?= htmlspecialchars((string) $account['opening_balance']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($accounts)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No accounts yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Account</h6>
                <form method="post" action="/accounting/accounts">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Bank - HDFC" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="account_type" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" step="0.01" name="opening_balance" class="form-control" value="0">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
