<?php
$pageTitle = 'Notice Board';
ob_start();

$renderNoticeCards = static function (array $items, bool $expired = false): void {
    if (empty($items)) {
        echo '<div class="text-center text-muted py-5">';
        echo $expired ? 'No expired notices.' : 'No active notices yet.';
        echo '</div>';
        return;
    }

    foreach ($items as $notice):
        $isExpired = $expired;
        ?>
        <div class="card border-0 shadow-sm mb-3 <?= $isExpired ? 'notice-card-expired' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="min-w-0">
                        <h6 class="mb-2">
                            <?= htmlspecialchars((string) $notice['title'], ENT_QUOTES, 'UTF-8') ?>
                            <span class="badge bg-<?= $notice['notice_type'] === 'circular' ? 'info' : 'primary' ?>"><?= ucfirst(htmlspecialchars((string) $notice['notice_type'], ENT_QUOTES, 'UTF-8')) ?></span>
                            <?php if ($isExpired): ?>
                                <span class="badge bg-secondary">Expired</span>
                            <?php endif; ?>
                        </h6>
                    </div>
                    <form method="post" action="/notices/<?= (int) $notice['id'] ?>/delete" onsubmit="return confirm('Remove this notice?');" class="flex-shrink-0">
                        <?= \\App\\Helpers\\Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete notice" title="Delete notice"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
                <p class="mb-3"><?= nl2br(htmlspecialchars((string) $notice['body'], ENT_QUOTES, 'UTF-8')) ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <small class="text-muted">Published <?= htmlspecialchars((string) $notice['published_at'], ENT_QUOTES, 'UTF-8') ?><?php if (!empty($notice['published_by_name'])): ?> by <?= htmlspecialchars((string) $notice['published_by_name'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></small>
                    <?php if (!empty($notice['expires_at'])): ?>
                        <small class="<?= $isExpired ? 'text-danger' : 'text-muted' ?>"><?= $isExpired ? 'Expired' : 'Expires' ?> <?= htmlspecialchars((string) $notice['expires_at'], ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    endforeach;
};
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h5 class="mb-0">Notice Board</h5>
    <div>
        <a href="/notices/events" class="btn btn-outline-secondary btn-sm me-2">Events</a>
        <a href="/notices/polls" class="btn btn-outline-secondary btn-sm">Polls</a>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <ul class="nav nav-tabs" id="notice-status-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-notices-tab" data-bs-toggle="tab" data-bs-target="#active-notices-panel" type="button" role="tab" aria-controls="active-notices-panel" aria-selected="true">
                Active <span class="badge bg-primary ms-1"><?= count($activeNotices) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="expired-notices-tab" data-bs-toggle="tab" data-bs-target="#expired-notices-panel" type="button" role="tab" aria-controls="expired-notices-panel" aria-selected="false">
                Expired <span class="badge bg-secondary ms-1"><?= count($expiredNotices) ?></span>
            </button>
        </li>
    </ul>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-app-Views-notices-index-php"><i class="fa-solid fa-plus me-1"></i>Publish Notice</button>
</div>

<div class="tab-content" id="notice-status-panels">
    <div class="tab-pane fade show active" id="active-notices-panel" role="tabpanel" aria-labelledby="active-notices-tab" tabindex="0">
        <?php $renderNoticeCards($activeNotices); ?>
    </div>
    <div class="tab-pane fade" id="expired-notices-panel" role="tabpanel" aria-labelledby="expired-notices-tab" tabindex="0">
        <div class="alert alert-secondary py-2 small"><i class="fa-regular fa-clock me-1"></i>Expired notices are kept for reference and are no longer shown as active.</div>
        <?php $renderNoticeCards($expiredNotices, true); ?>
    </div>
</div>

<div class="modal fade" id="modal-app-Views-notices-index-php" tabindex="-1" aria-labelledby="modal-app-Views-notices-index-php-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-app-Views-notices-index-php-label">Publish Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/notices">
                    <?= \\App\\Helpers\\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="notice-type">Type</label>
                        <select id="notice-type" name="notice_type" class="form-select">
                            <option value="notice">Notice</option>
                            <option value="circular">Circular</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notice-title">Title *</label>
                        <input id="notice-title" type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notice-body">Body *</label>
                        <textarea id="notice-body" name="body" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="notice-expires-at">Expires On</label>
                        <input id="notice-expires-at" type="datetime-local" name="expires_at" class="form-control">
                        <div class="form-text">After this date and time, the notice moves to the Expired tab.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Publish</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.notice-card-expired {
    opacity: .78;
    border-left: 4px solid var(--bs-secondary) !important;
}
#notice-status-tabs .nav-link { white-space: nowrap; }
</style>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
