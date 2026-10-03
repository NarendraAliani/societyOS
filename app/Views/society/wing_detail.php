<?php
$pageTitle = 'Wing ' . $wing['name'];
ob_start();
?>
<p><a href="/society/wings">&laquo; Back to Wings</a></p>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h6 class="mb-1">Configure Wing Structure</h6>
                <p class="text-muted small mb-0">
                    Generate missing floors and flats in one step. Existing floors and flats are never deleted automatically.
                </p>
            </div>
        </div>

        <form method="post" action="/society/wings/<?= (int) $wing['id'] ?>/configure" class="mt-3">
            <?= \App\Helpers\Csrf::field() ?>

            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Number of Floors</label>
                    <input
                        type="number"
                        name="floor_count"
                        class="form-control"
                        min="1"
                        max="100"
                        value="<?= count($floors) > 0 ? count($floors) : 1 ?>"
                        required
                    >
                    <div class="form-text">Existing floors are preserved.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Default Flats for New Floors</label>
                    <input
                        type="number"
                        name="default_flats_per_new_floor"
                        class="form-control"
                        min="1"
                        max="100"
                        value="<?= !empty($floors) ? max(1, (int) $floors[0]['flat_count']) : 1 ?>"
                        required
                    >
                    <div class="form-text">Used only when new floors are generated.</div>
                </div>

                <div class="col-md-5">
                    <button type="submit" class="btn btn-primary">Configure &amp; Generate</button>
                    <span class="text-muted small ms-2">Increasing a floor's flat count adds missing flats.</span>
                </div>
            </div>

            <?php if (!empty($floors)): ?>
                <div class="table-responsive mt-4">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Floor</th>
                                <th>Current Flats</th>
                                <th>Desired Flats</th>
                                <th>What happens</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($floors as $floor): ?>
                            <tr>
                                <td>
                                    <a href="/society/floors/<?= (int) $floor['id'] ?>">
                                        Floor <?= (int) $floor['floor_number'] ?>
                                    </a>
                                </td>
                                <td><?= (int) $floor['flat_count'] ?></td>
                                <td style="max-width: 180px;">
                                    <input
                                        type="number"
                                        name="flat_counts[<?= (int) $floor['id'] ?>]"
                                        class="form-control form-control-sm"
                                        min="<?= max(1, (int) $floor['flat_count']) ?>"
                                        max="100"
                                        value="<?= max(1, (int) $floor['flat_count']) ?>"
                                        required
                                    >
                                </td>
                                <td class="small text-muted">
                                    <?= (int) $floor['flat_count'] > 0
                                        ? 'Adds only missing flats; existing flats stay unchanged.'
                                        : 'Creates flats using the floor number sequence.' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="alert alert-info small mt-3 mb-0">
                <strong>Safety:</strong> The configurator will not delete a floor or flat. If you need fewer floors/flats,
                remove unused records manually after checking that they have no resident, billing, ownership, or other linked data.
            </div>
        </form>
    </div>
</div>

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
