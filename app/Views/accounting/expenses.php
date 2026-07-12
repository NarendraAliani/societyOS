<?php
$pageTitle = 'Expenses';
ob_start();
?>
<p><a href="/accounting/accounts">&laquo; Back to Accounts</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date</th><th>Category</th><th>Vendor</th><th>Account</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['expense_date']) ?></td>
                            <td><?= htmlspecialchars($entry['category']) ?></td>
                            <td><?= htmlspecialchars($entry['vendor_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($entry['account_name']) ?></td>
                            <td class="text-end text-danger fw-bold"><?= number_format((float) $entry['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($entries)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No expenses recorded yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Record Expense</h6>
                <form method="post" action="/accounting/expenses">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Account *</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int) $account['id'] ?>"><?= htmlspecialchars($account['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= (int) $vendor['id'] ?>"><?= htmlspecialchars($vendor['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Housekeeping" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Record Expense</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
