<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forgot Password - SocietyOS</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="/static/css/app.css" rel="stylesheet"></head>
<body class="bg-light"><div class="d-flex align-items-center justify-content-center min-vh-100 py-4"><div class="card shadow-sm" style="width:430px;max-width:calc(100vw - 2rem)"><div class="card-body p-4">
<h4 class="mb-2">Forgot Password</h4><p class="text-muted small mb-4">We'll send a secure password reset link to your registered email address.</p>
<?php if(!empty($message)): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if(!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" action="/forgot-password" autocomplete="off">
<?= \App\Helpers\Csrf::field() ?>
<div class="mb-3"><label class="form-label">Society Code <span class="text-muted">(for multi-society login)</span></label><input type="text" name="society_code" class="form-control" maxlength="30" placeholder="e.g. SOC-001"></div>
<div class="mb-3"><label class="form-label">Registered Email</label><input type="email" name="email" class="form-control" required autocomplete="email"></div>
<?php require __DIR__ . '/../components/captcha.php'; ?>
<button type="submit" class="btn btn-primary w-100">Email Reset Link</button></form>
<div class="text-center mt-3"><a href="/login">Back to Login</a></div>
</div></div></div></body></html>