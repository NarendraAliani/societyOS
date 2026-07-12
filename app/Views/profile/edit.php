<?php
$pageTitle = 'Edit Profile';
ob_start();
?>
<p><a href="/profile">&laquo; Back to Profile</a></p>
<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <form method="post" action="/profile">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['role_name']) ?>" disabled>
                <div class="form-text">Role changes are managed by an administrator under Admin &rarr; Users.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
