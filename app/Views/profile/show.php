<?php
$pageTitle = 'My Profile';
ob_start();
?>
<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <h6 class="mb-3"><?= htmlspecialchars($user['name']) ?></h6>
        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? '-') ?></p>
        <p class="mb-1"><strong>Role:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($user['role_name']) ?></span></p>
        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($user['status']) ?></span></p>
        <p class="mb-3"><strong>Last Login:</strong> <?= htmlspecialchars($user['last_login_at'] ?? 'This is your first login') ?></p>
        <a href="/profile/edit" class="btn btn-primary btn-sm">Edit Profile</a>
        <a href="/profile/password" class="btn btn-outline-primary btn-sm">Change Password</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
