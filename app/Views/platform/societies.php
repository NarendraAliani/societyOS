<?php $pageTitle='Platform — Societies'; ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Societies</h5>
        <small class="text-muted">Tenant administration</small>
    </div>
    <a href="/platform/societies/create" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus me-1"></i>Create Society
    </a>
</div>
<?php $flash=\App\Helpers\Flash::pull(); if($flash): ?>
    <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Code</th><th>Society</th><th>Location</th><th>Users</th><th>Wings</th><th>Created</th></tr></thead>
                <tbody>
                <?php foreach($societies as $society): ?>
                    <tr>
                        <td><span class="badge bg-dark"><?= htmlspecialchars($society['code']) ?></span></td>
                        <td class="fw-semibold"><?= htmlspecialchars($society['name']) ?></td>
                        <td><?= htmlspecialchars(trim(($society['city']??'').' '.($society['state']??''))) ?: '—' ?></td>
                        <td><?= (int)$society['user_count'] ?></td>
                        <td><?= (int)$society['wing_count'] ?></td>
                        <td><small><?= htmlspecialchars($society['created_at']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($societies)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No societies provisioned yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/platform.php'; ?>
