<?php
$pageTitle = 'My Complaints';
ob_start();
?>
<p><a href="/dashboard">&laquo; My Home</a></p>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5>My Complaints</h5>
                <table class="table table-hover align-middle">
                    <thead><tr><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Submitted</th></tr></thead>
                    <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <?php $badge = match ($complaint['status']) { 'resolved','closed' => 'success', 'in_progress' => 'warning', default => 'secondary' }; ?>
                        <tr><td><?= htmlspecialchars($complaint['subject']) ?></td><td><?= htmlspecialchars($complaint['category_name']) ?></td><td><?= ucfirst($complaint['priority']) ?></td><td><span class="badge bg-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?></span></td><td><small><?= htmlspecialchars($complaint['created_at']) ?></small></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$complaints): ?><tr><td colspan="5" class="text-center text-muted py-4">No complaints submitted.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Submit Complaint</h6>
                <form method="post" action="/resident/complaints">
                    <?= AppHelpersCsrf::field() ?>
                    <div class="mb-3"><label class="form-label">Category *</label><select name="category_id" class="form-select" required><option value="">Select</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Subject *</label><input name="subject" class="form-control" maxlength="150" required></div>
                    <div class="mb-3"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
                    <button class="btn btn-primary w-100" type="submit">Submit Complaint</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';