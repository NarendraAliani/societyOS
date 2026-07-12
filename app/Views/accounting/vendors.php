<?php
$pageTitle = 'Vendors';
ob_start();
?>
<p><a href="/accounting/accounts">&laquo; Back to Accounts</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Category</th><th>Contact</th><th>Phone</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td><?= htmlspecialchars($vendor['name']) ?></td>
                            <td><?= htmlspecialchars($vendor['category'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($vendor['contact_person'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($vendor['phone'] ?? '-') ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-vendor-<?= (int) $vendor['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                <form method="post" action="/accounting/vendors/<?= (int) $vendor['id'] ?>/delete" onsubmit="return confirm('Delete this vendor?');" class="d-inline">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-vendor-<?= (int) $vendor['id'] ?>">
                            <td colspan="5">
                                <form method="post" action="/accounting/vendors/<?= (int) $vendor['id'] ?>" class="row g-2 align-items-end p-2">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="col-md-3">
                                        <label class="form-label small">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($vendor['name']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Category</label>
                                        <input type="text" name="category" class="form-control form-control-sm" value="<?= htmlspecialchars($vendor['category'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Contact</label>
                                        <input type="text" name="contact_person" class="form-control form-control-sm" value="<?= htmlspecialchars($vendor['contact_person'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Phone</label>
                                        <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($vendor['phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Email</label>
                                        <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($vendor['email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($vendors)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No vendors yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Add Vendor</h6>
                <form method="post" action="/accounting/vendors">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Housekeeping">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Vendor</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
