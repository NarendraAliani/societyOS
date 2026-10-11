<?php
$pageTitle = 'Edit Role: ' . $role['name'];
ob_start();

$grouped = [];
foreach ($permissions as $permission) {
    $grouped[$permission['module']][] = $permission;
}
?>
<p><a href="/admin/roles">&laquo; Back to Roles</a></p>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6>Permissions for "<?= htmlspecialchars($role['name']) ?>"</h6>
        <form method="post" action="/admin/roles/<?= (int) $role['id'] ?>/permissions">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row">
                <?php foreach ($grouped as $module => $modulePermissions): ?>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-uppercase text-muted small"><?= htmlspecialchars($module) ?></h6>
                        <?php foreach ($modulePermissions as $permission): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permission_ids[]" value="<?= (int) $permission['id'] ?>"
                                    id="perm-<?= (int) $permission['id'] ?>" <?= in_array((int) $permission['id'], $grantedIds, true) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="perm-<?= (int) $permission['id'] ?>"><?= htmlspecialchars($permission['key']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Save Permissions</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
