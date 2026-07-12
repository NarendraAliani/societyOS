<?php
$pageTitle = 'Wing ' . $wing['name'];
ob_start();
?>
<p><a href="/society/wings">&laquo; Back to Wings</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Floors in Wing <?= htmlspecialchars($wing['name']) ?></h6>
                <table class="table table-hover align-middle">
                    <thead><tr><th>Floor #</th><th>Flats</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($floors as $floor): ?>
                        <tr>
                            <td><a href="/society/floors/<?= (int) $floor['id'] ?>">Floor <?= (int) $floor['floor_number'] ?></a></td>
                            <td><?= (int) $floor['flat_count'] ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-floor-<?= (int) $floor['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/society/floors/<?= (int) $floor['id'] ?>/delete" onsubmit="return confirm('Delete this floor and its flats?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-floor-<?= (int) $floor['id'] ?>">
                            <td colspan="3">
                                <form method="post" action="/society/floors/<?= (int) $floor['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-6">
                                        <label class="form-label small">Floor Number</label>
                                        <input type="number" name="floor_number" class="form-control form-control-sm" value="<?= (int) $floor['floor_number'] ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($floors)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No floors yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Floor</h6>
                <form method="post" action="/society/floors">
                    <?= \App\Helpers\Csrf::field() ?>
                    <input type="hidden" name="wing_id" value="<?= (int) $wing['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Floor Number</label>
                        <input type="number" name="floor_number" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Floor</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
