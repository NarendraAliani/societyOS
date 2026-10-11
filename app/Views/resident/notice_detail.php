<?php
$pageTitle = $notice['notice_type'] === 'circular' ? 'Circular Details' : 'Notice Details';
ob_start();
?>
<div class="mb-3">
    <a href="/dashboard" class="btn btn-outline-secondary btn-sm me-2"><i class="fa-solid fa-arrow-left me-1"></i>My Home</a>
    <a href="/resident/notices" class="btn btn-outline-secondary btn-sm">All Notices</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <span class="badge bg-<?= $notice['notice_type'] === 'circular' ? 'info' : 'primary' ?> mb-3"><?= htmlspecialchars(ucfirst($notice['notice_type'])) ?></span>
        <h4 class="mb-3"><?= htmlspecialchars($notice['title']) ?></h4>
        <div class="notice-body"><?= nl2br(htmlspecialchars($notice['body'])) ?></div>
        <hr>
        <small class="text-muted">Published <?= htmlspecialchars($notice['published_at']) ?></small>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
