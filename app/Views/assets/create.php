<?php
$pageTitle = 'Add Asset';
ob_start();
?>
<p><a href="/assets">&laquo; Back to Assets</a></p>
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body">
        <form method="post" action="/assets">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Purchase Cost</label>
                    <input type="number" step="0.01" name="purchase_cost" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Warranty Expiry</label>
                    <input type="date" name="warranty_expiry" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Add Asset</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
