<?php
$pageTitle = 'My Visitor Passes';
ob_start();
$activePasses = 0;
foreach ($passes as $pass) {
    if (!$pass['used_at'] && strtotime($pass['valid_until']) >= time()) {
        $activePasses++;
    }
}
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">Resident Services</div>
            <h4>Visitor Passes</h4>
            <p class="resident-subtitle">Create and track visitor passes for your home.</p>
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
                    <div class="stat-label">Total Passes</div>
                    <div class="stat-value"><?= count($passes) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-success resident-stat">
                <div class="card-body">
                    <div class="stat-label">Active</div>
                    <div class="stat-value"><?= $activePasses ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-secondary resident-stat">
                <div class="card-body">
                    <div class="stat-label">Used</div>
                    <div class="stat-value"><?= count(array_filter($passes, fn ($pass) => !empty($pass['used_at']))) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm kpi-card kpi-info resident-stat">
                <div class="card-body">
                    <div class="stat-label">Home</div>
                    <div class="stat-value fs-5"><?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card resident-section-card h-100">
                <div class="card-header">
                    <h5 class="resident-section-title">
                        <span class="resident-section-icon"><i class="fa-solid fa-user-plus"></i></span>
                        Create Visitor Pass
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Generate a one-time pass for your visitor. The gate can verify the pass from the Visitors module.</p>
                    <form method="post" action="/resident/visitor-passes">
                        <?= \App\Helpers\Csrf::field() ?>
                        <div class="mb-3">
                            <label class="form-label">Visitor Name <span class="text-danger">*</span></label>
                            <input name="visitor_name" class="form-control" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valid From <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="valid_from" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="valid_until" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="fa-solid fa-qrcode me-1"></i>Create Pass
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card resident-section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <h5 class="resident-section-title">
                        <span class="resident-section-icon"><i class="fa-solid fa-ticket"></i></span>
                        My Passes
                    </h5>
                    <span class="badge text-bg-secondary"><?= count($passes) ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle resident-table">
                            <thead>
                                <tr><th>Visitor</th><th>Pass Token</th><th>Valid Window</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($passes as $pass): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($pass['visitor_name']) ?></td>
                                    <td><code><?= htmlspecialchars($pass['qr_token']) ?></code></td>
                                    <td><small><?= htmlspecialchars($pass['valid_from']) ?> &rarr; <?= htmlspecialchars($pass['valid_until']) ?></small></td>
                                    <td>
                                        <?php if ($pass['used_at']): ?>
                                            <span class="badge text-bg-secondary">Used</span>
                                        <?php elseif (strtotime($pass['valid_until']) < time()): ?>
                                            <span class="badge text-bg-danger">Expired</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$passes): ?>
                                <tr><td colspan="4" class="resident-empty"><i class="fa-solid fa-ticket"></i>No visitor passes yet.<br><span class="small">Create a pass using the form.</span></td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';