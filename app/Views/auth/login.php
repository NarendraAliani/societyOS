<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - SocietyOS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/static/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="card shadow-sm" style="width: 380px; max-width: calc(100vw - 2rem);">
        <div class="card-body p-4">
            <h4 class="text-center mb-2"><i class="fa-solid fa-building"></i> SocietyOS</h4>
            <p class="text-center text-muted small mb-4">Sign in to your residential society</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="/login" autocomplete="off">
                <?= \App\Helpers\Csrf::field() ?>
                <div class="mb-3">
                    <label class="form-label">Society Code <span class="text-muted">(for multi-society login)</span></label>
                    <input type="text" name="society_code" class="form-control" maxlength="30" autocomplete="organization" placeholder="e.g. SOC-001">
                    <div class="form-text">Leave blank for the original single-society installation.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autofocus autocomplete="username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <?php require __DIR__ . '/../components/captcha.php'; ?>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="text-center mt-3"><a href="/forgot-password">Forgot Password?</a></div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
