<?php
$pageTitle = 'Asset Categories';
ob_start();
?>
<p><a href="/assets">&laquo; Back to Assets</a></p>
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-ac-<?= (int) $category['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/assets/categories/<?= (int) $category['id'] ?>/delete" onsubmit="return confirm('Delete this category?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-ac-<?= (int) $category['id'] ?>">
                            <td colspan="2">
                                <form method="post" action="/assets/categories/<?= (int) $category['id'] ?>" class="input-group input-group-sm p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="2" class="text-center text-muted py-4">No categories yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Category</h6>
                <form method="post" action="/assets/categories">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" required>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
