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
    <div class="col-md-4 d-flex justify-content-md-end align-items-start"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-app-Views-accounting-expenses-php"><i class="fa-solid fa-plus me-1"></i>Record Expense</button><div class="modal fade" id="modal-app-Views-accounting-expenses-php" tabindex="-1" aria-labelledby="modal-app-Views-accounting-expenses-php-label" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="modal-app-Views-accounting-expenses-php-label">Record Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
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
                        <div class="input-group">
                            <select name="category" class="form-select" required>
                                <option value="">Select expense category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-expense-category-add" title="Add a new expense category"><i class="fa-solid fa-plus"></i> Add</button>
                        </div>
                        <?php if (empty($categories)): ?><div class="form-text">No categories yet. Use Add to create your first category.</div><?php endif; ?>
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
                </form></div></div></div></div></div>
</div>
<div class="modal fade" id="modal-expense-category-add" tabindex="-1" aria-labelledby="modal-expense-category-add-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-expense-category-add-label">Add Expense Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/accounting/expense-categories">
                <div class="modal-body">
                    <?= \App\Helpers\Csrf::field() ?>
                    <label class="form-label" for="new-expense-category">Category name *</label>
                    <input type="text" id="new-expense-category" name="name" class="form-control" maxlength="100" placeholder="e.g. Lift Maintenance" required>
                    <div class="form-text">Once saved, this category will be available in the Record Expense dropdown for this society.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
