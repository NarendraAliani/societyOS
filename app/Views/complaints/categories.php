<?php
$pageTitle = 'Complaint Categories';
ob_start();
?>
<p><a href="/complaints">&laquo; Back to Complaints</a></p>
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
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-cc-<?= (int) $category['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/complaints/categories/<?= (int) $category['id'] ?>/delete" onsubmit="return confirm('Delete this category?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-cc-<?= (int) $category['id'] ?>">
                            <td colspan="2">
                                <form method="post" action="/complaints/categories/<?= (int) $category['id'] ?>" class="input-group input-group-sm p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5 d-flex justify-content-md-end align-items-start"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-app-Views-complaints-categories-php"><i class="fa-solid fa-plus me-1"></i>Add Category</button><div class="modal fade" id="modal-app-Views-complaints-categories-php" tabindex="-1" aria-labelledby="modal-app-Views-complaints-categories-php-label" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="modal-app-Views-complaints-categories-php-label">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
                <h6>Add Category</h6>
                <form method="post" action="/complaints/categories">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" required>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form></div></div></div></div></div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
