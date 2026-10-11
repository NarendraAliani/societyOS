<?php
$pageTitle = 'Complaints';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Complaints</h5>
    <div>
        <a href="/complaints/categories" class="btn btn-outline-secondary btn-sm me-2">Categories</a>
        <a href="/complaints/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Register Complaint</a>
    </div>
</div>

<form method="get" action="/complaints" class="mb-3" style="max-width: 220px;">
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        <?php foreach (['open', 'in_progress', 'resolved', 'closed'] as $s): ?>
            <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Subject</th><th>Flat</th><th>Category</th><th>Priority</th><th>Status</th><th>Raised</th></tr></thead>
            <tbody>
            <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td><a href="/complaints/<?= (int) $complaint['id'] ?>"><?= htmlspecialchars($complaint['subject']) ?></a></td>
                    <td><?= htmlspecialchars($complaint['wing_name'] . '-' . $complaint['flat_number']) ?></td>
                    <td><?= htmlspecialchars($complaint['category_name']) ?></td>
                    <td>
                        <?php $pbadge = match ($complaint['priority']) { 'high' => 'danger', 'medium' => 'warning', default => 'secondary' }; ?>
                        <span class="badge bg-<?= $pbadge ?>"><?= ucfirst($complaint['priority']) ?></span>
                    </td>
                    <td>
                        <?php $sbadge = match ($complaint['status']) { 'resolved' => 'success', 'closed' => 'secondary', 'in_progress' => 'info', default => 'warning' }; ?>
                        <span class="badge bg-<?= $sbadge ?>"><?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?></span>
                    </td>
                    <td><small><?= htmlspecialchars($complaint['created_at']) ?></small></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($complaints)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No complaints found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
