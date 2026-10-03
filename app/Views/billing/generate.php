<?php
$pageTitle = 'Generate Bills';
ob_start();
?>
<p><a href="/billing">&laquo; Back to Bills</a></p>
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body">
        <p class="text-muted">Generates one bill per flat using all active maintenance heads. Flats already billed for the exact same period are skipped automatically.</p>
        <form method="post" action="/billing/generate">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label">Financial Year *</label>
                <select name="financial_year_id" class="form-select" required>
                    <option value="">Select</option>
                    <?php foreach ($financialYears as $fy): ?>
                        <option value="<?= (int) $fy['id'] ?>" <?= $fy['is_current'] ? 'selected' : '' ?>><?= htmlspecialchars($fy['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Period Start *</label>
                    <input type="date" name="period_start" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Period End *</label>
                    <input type="date" name="period_end" class="form-control" required>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label class="form-label">Due Date *</label>
                <input type="date" name="due_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Generate Bills</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
