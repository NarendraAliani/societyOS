<?php
$pageTitle = 'My Complaints';
ob_start();
$openComplaints = 0;
$resolvedComplaints = 0;
foreach ($complaints as $complaint) {
    if (in_array($complaint['status'], ['resolved', 'closed'], true)) {
        $resolvedComplaints++;
    } else {
        $openComplaints++;
    }
}
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">Resident Services</div>
            <h4>My Complaints</h4>
            <p class="resident-subtitle">Raise an issue and keep track of its status.</p>
        </div>
        <div class="resident-home-pill">
            <i class="fa-solid fa-house"></i>
            <span><?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-primary resident-stat">
                <div class="card-body">
                    <div class="stat-label">Total</div>
                    <div class="stat-value"><?= count($complaints) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-warning resident-stat">
                <div class="card-body">
                    <div class="stat-label">Open</div>
                    <div class="stat-value"><?= $openComplaints ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-success resident-stat">
                <div class="card-body">
                    <div class="stat-label">Resolved / Closed</div>
                    <div class="stat-value"><?= $resolvedComplaints ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-info resident-stat">
                <div class="card-body">
                    <div class="stat-label">Categories</div>
                    <div class="stat-value"><?= count($categories) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <h5 class="resident-section-title">
                        <span class="resident-section-icon"><i class="fa-solid fa-list-check"></i></span>
                        Complaint History
                    </h5>
                    <span class="badge text-bg-secondary"><?= count($complaints) ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle resident-table">
                            <thead>
                                <tr><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Submitted</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                                <?php $badge = match ($complaint['status']) { 'resolved','closed' => 'success', 'in_progress' => 'warning', default => 'secondary' }; ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($complaint['subject']) ?></td>
                                    <td><?= htmlspecialchars($complaint['category_name']) ?></td>
                                    <td>
                                        <?php $priorityBadge = match ($complaint['priority']) { 'high' => 'danger', 'medium' => 'warning', default => 'secondary' }; ?>
                                        <span class="badge text-bg-<?= $priorityBadge ?>"><?= ucfirst($complaint['priority']) ?></span>
                                    </td>
                                    <td><span class="badge text-bg-<?= $badge ?>"><?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?></span></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($complaint['created_at']) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$complaints): ?>
                                <tr><td colspan="5" class="resident-empty"><i class="fa-solid fa-circle-check"></i>No complaints submitted.<br><span class="small">Use the form to raise an issue with the society.</span></td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card resident-section-card h-100">
                <div class="card-header">
                    <h5 class="resident-section-title">
                        <span class="resident-section-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                        Submit Complaint
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/resident/complaints">
                        <?= \App\Helpers\Csrf::field() ?>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input name="subject" class="form-control" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Describe the issue clearly"></textarea>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="fa-solid fa-paper-plane me-1"></i>Submit Complaint
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';