<?php
$pageTitle = 'Roles & Permissions';
ob_start();
?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Role</th><th>Description</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= htmlspecialchars($role['name']) ?></td>
                    <td><?= htmlspecialchars($role['description'] ?? '') ?></td>
                    <td class="text-end">
                        <?php if ($role['name'] === 'super_admin'): ?>
                            <span class="text-muted small">All permissions (fixed)</span>
                        <?php else: ?>
                            <a href="/admin/roles/<?= (int) $role['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit Permissions</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
