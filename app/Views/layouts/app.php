<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'SocietyOS') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
<link href="/static/css/app.css" rel="stylesheet">
</head>
<body>
<div class="d-flex" id="app-shell">
    <nav class="sidebar bg-dark text-white p-3" style="width:250px;min-height:100vh;">
        <h4 class="mb-4"><i class="fa-solid fa-building"></i> SocietyOS</h4>
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item"><a class="nav-link text-white" href="/dashboard"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/society"><i class="fa-solid fa-sliders me-2"></i>Society Setup</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/society/wings"><i class="fa-solid fa-sitemap me-2"></i>Wings &amp; Flats</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/society/maintenance-heads"><i class="fa-solid fa-coins me-2"></i>Maintenance Config</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/members"><i class="fa-solid fa-users me-2"></i>Residents</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/vehicles"><i class="fa-solid fa-car me-2"></i>Vehicles</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/billing"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Maintenance</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/accounting/accounts"><i class="fa-solid fa-scale-balanced me-2"></i>Accounts</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/visitors"><i class="fa-solid fa-id-card me-2"></i>Visitors</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/complaints"><i class="fa-solid fa-triangle-exclamation me-2"></i>Complaints</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/notices"><i class="fa-solid fa-bullhorn me-2"></i>Notices</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/staff"><i class="fa-solid fa-user-tie me-2"></i>Staff</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/assets"><i class="fa-solid fa-toolbox me-2"></i>Assets</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/reports"><i class="fa-solid fa-chart-column me-2"></i>Reports</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/admin/users"><i class="fa-solid fa-user-shield me-2"></i>Administration</a></li>
        </ul>
    </nav>
    <main class="flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-3">
            <span class="navbar-text"><?= htmlspecialchars($pageTitle ?? '') ?></span>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/profile">Profile</a></li>
                    <li><a class="dropdown-item" href="/profile/password">Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="post" action="/logout">
                            <?= \App\Helpers\Csrf::field() ?>
                            <button class="dropdown-item" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="p-4">
            <?php $flash = \App\Helpers\Flash::pull(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
