<?php
$pageTitle = 'Backup & Restore';
ob_start();
?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>

<div class="alert alert-warning">
    <strong>Restoring overwrites the entire database.</strong> This cannot be undone by clicking a button again —
    a safety backup of the current state is taken automatically before any restore runs, but everything created
    or changed after that safety backup's timestamp will be lost if you restore. Only <code>super_admin</code>
    can access this page.
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6>Create Backup</h6>
        <p class="text-muted small mb-2">Generates a full database dump (structure + data, all tables) and saves it on the server.</p>
        <form method="post" action="/admin/backup">
            <?= \App\Helpers\Csrf::field() ?>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-database me-1"></i>Create Backup Now</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6>Restore from Uploaded File</h6>
        <form method="post" action="/admin/backup/restore-upload" enctype="multipart/form-data"
              onsubmit="return confirm('This will overwrite the entire database with the contents of the uploaded file. A safety backup of the current state will be taken first. Continue?');">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-2">
                <div class="col-auto"><input type="file" name="restore_file" accept=".sql" class="form-control form-control-sm" required></div>
                <div class="col-auto"><button type="submit" class="btn btn-outline-danger btn-sm">Restore</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6>Backup History</h6>
        <table class="table table-sm align-middle">
            <thead><tr><th>Created</th><th>Filename</th><th>Size</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($backups as $backup): ?>
                <tr>
                    <td><?= htmlspecialchars($backup['created_at']) ?></td>
                    <td><code><?= htmlspecialchars($backup['filename']) ?></code></td>
                    <td><?= number_format($backup['size'] / 1024, 1) ?> KB</td>
                    <td class="text-end">
                        <a href="/admin/backup/<?= urlencode($backup['filename']) ?>/download" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download"></i></a>
                        <form method="post" action="/admin/backup/<?= urlencode($backup['filename']) ?>/restore" class="d-inline"
                              onsubmit="return confirm('Restore the database to the state in \'<?= htmlspecialchars($backup['filename'], ENT_QUOTES) ?>\'? This overwrites all current data. A safety backup of the current state will be taken first. Continue?');">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button type="submit" class="btn btn-outline-warning btn-sm">Restore</button>
                        </form>
                        <form method="post" action="/admin/backup/<?= urlencode($backup['filename']) ?>/delete" class="d-inline"
                              onsubmit="return confirm('Delete this backup file? This cannot be undone.');">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($backups)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No backups yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
