<?php
$pageTitle = 'Assets';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Asset Register</h5>
    <div>
        <a href="/assets/categories" class="btn btn-outline-secondary btn-sm me-2">Categories</a>
        <a href="/assets/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add Asset</a>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Name</th><th>Category</th><th>Location</th><th>Purchase Cost</th><th>Warranty Expiry</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><a href="/assets/<?= (int) $asset['id'] ?>"><?= htmlspecialchars($asset['name']) ?></a></td>
                    <td><?= htmlspecialchars($asset['category_name']) ?></td>
                    <td><?= htmlspecialchars($asset['location'] ?? '-') ?></td>
                    <td><?= $asset['purchase_cost'] !== null ? number_format((float) $asset['purchase_cost'], 2) : '-' ?></td>
                    <td><?= htmlspecialchars($asset['warranty_expiry'] ?? '-') ?></td>
                    <td>
                        <?php $badge = match ($asset['status']) { 'active' => 'success', 'under_repair' => 'warning', default => 'secondary' }; ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $asset['status'])) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($assets)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No assets registered yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
