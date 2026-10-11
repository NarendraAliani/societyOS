<?php
$pageTitle = $complaint['subject'];
ob_start();
?>
<p><a href="/complaints">&laquo; Back to Complaints</a></p>
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6><?= htmlspecialchars($complaint['subject']) ?></h6>
                <p class="mb-1"><strong>Flat:</strong> <?= htmlspecialchars($complaint['wing_name'] . '-' . $complaint['flat_number']) ?></p>
                <p class="mb-1"><strong>Raised by:</strong> <?= htmlspecialchars($complaint['member_name']) ?></p>
                <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($complaint['category_name']) ?></p>
                <p class="mb-1"><strong>Priority:</strong> <?= ucfirst($complaint['priority']) ?></p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?></span></p>
                <?php if ($complaint['description']): ?>
                    <p class="mt-3"><?= nl2br(htmlspecialchars($complaint['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!in_array($complaint['status'], ['closed'], true)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6>Update Status</h6>
                <form method="post" action="/complaints/<?= (int) $complaint['id'] ?>/updates">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="open" <?= $complaint['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="in_progress" <?= $complaint['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="resolved" <?= $complaint['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Status Timeline</h6>
                <ul class="list-group list-group-flush">
                    <?php foreach ($updates as $update): ?>
                        <li class="list-group-item">
                            <span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $update['status'])) ?></span>
                            <small class="text-muted"><?= htmlspecialchars($update['created_at']) ?></small>
                            <?php if ($update['updated_by_name']): ?><small class="text-muted"> by <?= htmlspecialchars($update['updated_by_name']) ?></small><?php endif; ?>
                            <?php if ($update['remarks']): ?><div><?= htmlspecialchars($update['remarks']) ?></div><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($updates)): ?>
                        <li class="list-group-item text-muted">No updates yet.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
