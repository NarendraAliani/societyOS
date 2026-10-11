<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset Password - SocietyOS</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="d-flex align-items-center justify-content-center min-vh-100 py-4"><div class="card shadow-sm" style="width:430px;max-width:calc(100vw - 2rem)"><div class="card-body p-4">
<h4 class="mb-2">Reset Password</h4>
<?php if(!empty($message)): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><a href="/login" class="btn btn-primary w-100">Go to Login</a>
<?php elseif(!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><a href="/forgot-password" class="btn btn-outline-primary w-100">Request a New Link</a>
<?php elseif(!empty($valid)): ?><p class="text-muted small mb-4">Choose a new password. This email link can be used only once.</p>
<form method="post" action="/reset-password"><?= \App\Helpers\Csrf::field() ?><input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>"><div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" minlength="8" required autocomplete="new-password"></div>
<div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="password_confirmation" class="form-control" minlength="8" required autocomplete="new-password"></div>
<button type="submit" class="btn btn-primary w-100">Reset Password</button></form>
<?php else: ?><div class="alert alert-danger">This password reset link is invalid, expired, or already used.</div><a href="/forgot-password" class="btn btn-outline-primary w-100">Request a New Link</a><?php endif; ?>
</div></div></div></body></html>