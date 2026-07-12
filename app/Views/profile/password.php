<?php
$pageTitle = 'Change Password';
ob_start();
?>
<p><a href="/profile">&laquo; Back to Profile</a></p>
<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <form method="post" action="/profile/password">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label">Current Password *</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">New Password *</label>
                <input type="password" name="new_password" class="form-control" minlength="8" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password *</label>
                <input type="password" name="confirm_password" class="form-control" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Change Password</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
