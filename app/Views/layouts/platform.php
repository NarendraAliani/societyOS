<?php $pageTitle = $pageTitle ?? 'SocietyOS Platform'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
<link href="/static/css/app.css" rel="stylesheet">
<script>
(function () {
    var theme = localStorage.getItem('societyos-theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
})();
</script>
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg bg-body border-bottom">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="/platform/societies">
            <i class="fa-solid fa-building-shield me-2"></i>SocietyOS Platform
        </a>
        <?php if (\App\Helpers\PlatformAuth::check()): ?>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline"><?= htmlspecialchars($_SESSION['platform_admin_name'] ?? 'Platform Administrator') ?></span>
                <?php if (!\App\Helpers\PlatformAuth::mustChangePassword()): ?>
                    <a class="btn btn-outline-secondary btn-sm" href="/platform/password">Password</a>
                <?php endif; ?>
                <a class="btn btn-outline-danger btn-sm" href="/platform/logout">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<main class="container py-4">
    <?= $content ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
