<?php
ob_start();
?>
<p><a href="/accounting/income-details">&laquo; Back to Income Details</a></p>
<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Edit Manual Income</h5>
                <p class="text-muted small">Changes update the linked account ledger entry as well.</p>
                <form method="post" action="/accounting/income/<?= (int) $entry['id'] ?>">
                    <?= \\App\\Helpers\\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="income-account">Account *</label>
                        <select id="income-account" name="account_id" class="form-select" required>
                            <option value="">Select account</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int) $account['id'] ?>" <?= (int) $entry['account_id'] === (int) $account['id'] ? 'selected' : '' ?>><?= htmlspecialchars($account['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="income-category">Category *</label>
                        <input id="income-category" type="text" name="category" class="form-control" value="<?= htmlspecialchars((string) $entry['category']) ?>" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="income-amount">Amount *</label>
                        <input id="income-amount" type="number" name="amount" class="form-control" min="0.01" step="0.01" value="<?= htmlspecialchars((string) $entry['amount']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="income-date">Date *</label>
                        <input id="income-date" type="date" name="income_date" class="form-control" value="<?= htmlspecialchars((string) $entry['income_date']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="income-description">Description</label>
                        <input id="income-description" type="text" name="description" class="form-control" value="<?= htmlspecialchars((string) ($entry['description'] ?? '')) ?>" maxlength="1000">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/accounting/income-details" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
