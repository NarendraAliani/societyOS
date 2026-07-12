<?php
$pageTitle = 'Users';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Users</h5>
    <div>
        <a href="/admin/roles" class="btn btn-outline-secondary btn-sm me-2">Roles &amp; Permissions</a>
        <a href="/admin/activity-logs" class="btn btn-outline-secondary btn-sm me-2">Activity Logs</a>
        <?php if (\App\Helpers\Auth::role() === 'super_admin'): ?>
            <a href="/admin/backup" class="btn btn-outline-secondary btn-sm me-2">Backup &amp; Restore</a>
        <?php endif; ?>
        <a href="/admin/users/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add User</a>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($user['role_name']) ?></span></td>
                    <td>
                        <?php $badge = match ($user['status']) { 'active' => 'success', 'locked' => 'danger', default => 'secondary' }; ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($user['status']) ?></span>
                    </td>
                    <td><small><?= htmlspecialchars($user['last_login_at'] ?? 'Never') ?></small></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-<?= (int) $user['id'] ?>">Edit</button>
                    </td>
                </tr>
                <tr class="collapse" id="edit-<?= (int) $user['id'] ?>">
                    <td colspan="6">
                        <div class="row g-3 p-2">
                            <div class="col-md-6">
                                <form method="post" action="/admin/users/<?= (int) $user['id'] ?>">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small">Role</label>
                                            <select name="role_id" class="form-select form-select-sm">
                                                <?php foreach ($roles ?? [] as $role): ?>
                                                    <option value="<?= (int) $role['id'] ?>" <?= $role['id'] === $user['role_id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Status</label>
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                <option value="locked" <?= $user['status'] === 'locked' ? 'selected' : '' ?>>Locked</option>
                                            </select>
                                        </div>
                                        <div class="col-12"><button type="submit" class="btn btn-sm btn-primary">Save</button></div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="post" action="/admin/users/<?= (int) $user['id'] ?>/reset-password">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <label class="form-label small">Reset Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="password" class="form-control" placeholder="New password (min 8 chars)" minlength="8" required>
                                        <button type="submit" class="btn btn-outline-danger">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No users yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
