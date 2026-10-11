<?php
$pageTitle = 'Society Notices';
ob_start();
?>
<p><a href="/dashboard">&laquo; My Home</a></p>
<div class="row">
<?php foreach ($notices as $notice): ?>
    <div class="col-lg-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="badge bg-<?= $notice['notice_type'] === 'circular' ? 'info' : 'primary' ?> mb-2"><?= ucfirst($notice['notice_type']) ?></span>
                <h6><?= htmlspecialchars($notice['title']) ?></h6>
                <div><?= nl2br(htmlspecialchars($notice['body'])) ?></div>
                <small class="text-muted d-block mt-3">Published <?= htmlspecialchars($notice['published_at']) ?></small>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php if (!$notices): ?><div class="col-12 text-center text-muted py-5">No active notices.</div><?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';