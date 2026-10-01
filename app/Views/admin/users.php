<?php
$pageTitle = 'Users';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Users</h5>
    <div>
        <a href="/admin/roles" class="btn btn-outline-secondary btn-sm me-2">Roles &amp; Permissions</a>
        <a href="/admin/activity-logs" class="btn btn-outline-secondary btn-sm me-2">Activity Logs</a>
        <?php if (\App\Helpers\Auth::can('settings.manage')): ?>
            <a href="/admin/settings" class="btn btn-outline-secondary btn-sm me-2">Settings</a>
        <?php endif; ?>
        <?php if (\App\Helpers\Auth::role() === 'super_admin'): ?>
            <a href="/admin/backup" class="btn btn-outline-secondary btn-sm me-2">Backup &amp; Restore</a>
        <?php endif; ?>
        <a href="/admin/users/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add User</a>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Resident / Home</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($user['role_name']) ?></span></td>
                    <td>
                        <?php if (!empty($user['member_id']) && !empty($user['linked_flat_number'])): ?>
                            <span class="fw-semibold"><?= htmlspecialchars($user['linked_wing_name'] . '-' . $user['linked_flat_number']) ?></span>
                            <small class="text-muted d-block"><?= htmlspecialchars($user['linked_member_name']) ?></small>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
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
                    <td colspan="7">
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
                                            <label class="form-label small">Linked Resident</label>
                                            <select name="member_id" class="form-select form-select-sm js-linked-home" data-user-id="<?= (int) $user['id'] ?>" data-current-member-id="<?= (int) ($user['member_id'] ?? 0) ?>" <?= in_array($user['role_name'], ['resident', 'tenant'], true) ? '' : 'disabled' ?>>
                                                <?php if (!in_array($user['role_name'], ['resident', 'tenant'], true)): ?>
                                                    <option value="0">Not linked</option>
                                                <?php else: ?>
                                                    <option value="<?= (int) ($user['member_id'] ?? 0) ?>"><?= htmlspecialchars(($user['linked_wing_name'] ?? '') . '-' . ($user['linked_flat_number'] ?? '') . ' — ' . ($user['linked_member_name'] ?? 'Current resident')) ?></option>
                                                <?php endif; ?>
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
                                        <div class="col-12"><small class="text-muted">Every role is linked to an active resident/member. The same resident may hold different roles independently, but only once for each role.</small></div>
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
                <tr><td colspan="7" class="text-center text-muted py-4">No users yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
(function () {
    async function loadResidents(form) {
        const role = form.querySelector('select[name="role_id"]');
        const resident = form.querySelector('.js-linked-home');
        if (!role || !resident) return;

        const roleName = role.options[role.selectedIndex]?.textContent.trim() || '';
        resident.disabled = true;
        resident.innerHTML = '';

        resident.append(new Option('Loading available residents…', '0'));
        try {
            const response = await fetch('/admin/users/resident-candidates?role_id=' + encodeURIComponent(role.value) + '&user_id=' + encodeURIComponent(resident.dataset.userId), {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error();
            const data = await response.json();
            resident.innerHTML = '';

            if (!data.items || data.items.length === 0) {
                resident.append(new Option('No available residents', '0'));
                return;
            }

            resident.append(new Option('Select resident', '0'));
            data.items.forEach(function (item) {
                resident.append(new Option(item.wing_name + '-' + item.flat_number + ' — ' + item.name + ' (' + item.member_type + ')', item.id));
            });

            const current = resident.dataset.currentMemberId;
            if (current && Array.from(resident.options).some(function (option) { return option.value === current; })) {
                resident.value = current;
            }
            resident.disabled = false;
        } catch (error) {
            resident.innerHTML = '';
            resident.append(new Option('Could not load available residents', '0'));
        }
    }

    document.querySelectorAll('tr.collapse form').forEach(function (form) {
        const role = form.querySelector('select[name="role_id"]');
        if (!role || !form.querySelector('.js-linked-home')) return;
        role.addEventListener('change', function () { loadResidents(form); });
        loadResidents(form);
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
