<?php
$pageTitle = 'Notice Board';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Notice Board</h5>
    <div>
        <a href="/notices/events" class="btn btn-outline-secondary btn-sm me-2">Events</a>
        <a href="/notices/polls" class="btn btn-outline-secondary btn-sm">Polls</a>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <?php foreach ($notices as $notice): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6><?= htmlspecialchars($notice['title']) ?>
                            <span class="badge bg-<?= $notice['notice_type'] === 'circular' ? 'info' : 'primary' ?>"><?= ucfirst($notice['notice_type']) ?></span>
                        </h6>
                        <form method="post" action="/notices/<?= (int) $notice['id'] ?>/delete" onsubmit="return confirm('Remove this notice?');">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                    <p><?= nl2br(htmlspecialchars($notice['body'])) ?></p>
                    <small class="text-muted">Published <?= htmlspecialchars($notice['published_at']) ?><?php if ($notice['published_by_name']): ?> by <?= htmlspecialchars($notice['published_by_name']) ?><?php endif; ?></small>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($notices)): ?>
            <div class="text-center text-muted py-5">No notices published yet.</div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Publish Notice</h6>
                <form method="post" action="/notices">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="notice_type" class="form-select">
                            <option value="notice">Notice</option>
                            <option value="circular">Circular</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Body *</label>
                        <textarea name="body" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expires On</label>
                        <input type="datetime-local" name="expires_at" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Publish</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
