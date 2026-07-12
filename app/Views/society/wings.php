<?php
$pageTitle = 'Wings & Flats';
ob_start();
?>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Wing</th><th>Floors</th><th>Flats</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($wings as $wing): ?>
                        <tr>
                            <td><a href="/society/wings/<?= (int) $wing['id'] ?>"><?= htmlspecialchars($wing['name']) ?></a></td>
                            <td><?= (int) $wing['floor_count'] ?></td>
                            <td><?= (int) $wing['flat_count'] ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-wing-<?= (int) $wing['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/society/wings/<?= (int) $wing['id'] ?>/delete" onsubmit="return confirm('Delete this wing and everything under it?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-wing-<?= (int) $wing['id'] ?>">
                            <td colspan="4">
                                <form method="post" action="/society/wings/<?= (int) $wing['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-6">
                                        <label class="form-label small">Wing Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($wing['name']) ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($wings)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No wings yet. Add one to get started.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Wing</h6>
                <form method="post" action="/society/wings">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Wing Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. A, Tower-1" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Wing</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
