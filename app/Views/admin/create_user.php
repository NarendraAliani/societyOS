<?php
$pageTitle = 'Add User';
ob_start();
?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body">
        <form method="post" action="/admin/users">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Role *</label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
            </div>
            <p class="text-muted small mt-2">User will be required to change this password on first login.</p>
            <button type="submit" class="btn btn-primary mt-2">Create User</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
