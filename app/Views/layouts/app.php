<?php
// Site-wide defaults from Settings — only used as the fallback before a browser has its own
// localStorage preference (set the first time a user picks Theme/Font Size from the topbar).
$siteThemeDefault = \App\Models\Settings::get((int) ($_SESSION['society_id'] ?? 0), 'theme_default', 'light');
$siteFontSizeDefault = \App\Models\Settings::get((int) ($_SESSION['society_id'] ?? 0), 'font_size_default', 'medium');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'SocietyOS') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
<link href="/static/css/app.css" rel="stylesheet">
<script>
(function () {
    var theme = localStorage.getItem('societyos-theme') || <?= json_encode($siteThemeDefault) ?>;
    document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
    if (theme === 'mid') {
        document.documentElement.setAttribute('data-theme', 'mid');
    }
    var fontSize = localStorage.getItem('societyos-font-size') || <?= json_encode($siteFontSizeDefault) ?>;
    if (fontSize !== 'medium') {
        document.documentElement.setAttribute('data-font-size', fontSize);
    }
})();
</script>
</head>
<body>
<div class="d-flex" id="app-shell">
    <nav class="sidebar bg-dark text-white p-3" id="app-sidebar" aria-label="Primary navigation">

        <h4 class="mb-4"><i class="fa-solid fa-building"></i> SocietyOS</h4>
        <ul class="nav nav-pills flex-column gap-1">
            <?php if (\App\Helpers\Auth::isResident()): ?>
                <li class="nav-item"><a class="nav-link text-white" href="/dashboard"><i class="fa-solid fa-house me-2"></i>My Home</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/resident/bills"><i class="fa-solid fa-file-invoice-dollar me-2"></i>My Bills</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/resident/visitor-passes"><i class="fa-solid fa-user-plus me-2"></i>Visitor Passes</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/resident/family"><i class="fa-solid fa-people-roof me-2"></i>My Family</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/resident/complaints"><i class="fa-solid fa-triangle-exclamation me-2"></i>My Complaints</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/resident/notices"><i class="fa-solid fa-bullhorn me-2"></i>Society Notices</a></li>
            <?php else: ?>
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
            <?php endif; ?>
        </ul>
    </nav>
    <main class="flex-grow-1">
        <nav class="navbar app-topbar border-bottom px-3" aria-label="Application toolbar">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button class="btn btn-outline-secondary app-sidebar-toggle d-lg-none" type="button" aria-label="Open navigation" aria-controls="app-sidebar" aria-expanded="false">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="navbar-text text-truncate"><?= htmlspecialchars($pageTitle ?? '') ?></span>
            </div>
            <div class="d-flex align-items-center gap-2 app-topbar-actions">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Theme">
                        <i class="fa-solid fa-circle-half-stroke me-1"></i>Theme
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item theme-option" type="button" data-theme-value="light"><i class="fa-solid fa-sun me-2"></i>Light</button></li>
                        <li><button class="dropdown-item theme-option" type="button" data-theme-value="dark"><i class="fa-solid fa-moon me-2"></i>Dark</button></li>
                        <li><button class="dropdown-item theme-option" type="button" data-theme-value="mid"><i class="fa-solid fa-circle-half-stroke me-2"></i>Mid</button></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Font Size">
                        <i class="fa-solid fa-text-height me-1"></i>Font
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item font-size-option" type="button" data-font-size-value="small">Small</button></li>
                        <li><button class="dropdown-item font-size-option" type="button" data-font-size-value="medium">Medium</button></li>
                        <li><button class="dropdown-item font-size-option" type="button" data-font-size-value="large">Large</button></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-tag me-1"></i><?= htmlspecialchars(\App\Helpers\Auth::role() ?? 'Role') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (\App\Helpers\Auth::roles() as $accountRole): ?>
                            <li><form method="post" action="/switch-role" class="m-0"><?= \App\Helpers\Csrf::field() ?><input type="hidden" name="role_id" value="<?= (int)$accountRole['role_id'] ?>"><button class="dropdown-item <?= ((int)$accountRole['role_id'] === (int)\App\Helpers\Auth::roleId()) ? 'active' : '' ?>" type="submit"><?= htmlspecialchars($accountRole['role_name']) ?></button></form></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile">Profile</a></li>
                        <li><a class="dropdown-item" href="/profile/password">Change Password</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="p-4" id="app-content">
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
<script>
(function () {
    var shell = document.getElementById('app-shell');
    var sidebarToggle = document.querySelector('.app-sidebar-toggle');
    var sidebar = document.getElementById('app-sidebar');

    function setSidebarOpen(open) {
        if (!shell || !sidebarToggle) return;
        shell.classList.toggle('sidebar-open', open);
        sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            setSidebarOpen(!shell.classList.contains('sidebar-open'));
        });
    }

    document.addEventListener('click', function (event) {
        if (!shell || !shell.classList.contains('sidebar-open') || !sidebar) return;
        if (sidebar.contains(event.target) || sidebarToggle.contains(event.target)) return;
        if (window.innerWidth < 992) setSidebarOpen(false);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) setSidebarOpen(false);
    });

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
        if (theme === 'mid') {
            document.documentElement.setAttribute('data-theme', 'mid');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
        localStorage.setItem('societyos-theme', theme);
    }
    document.querySelectorAll('.theme-option').forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyTheme(btn.getAttribute('data-theme-value'));
        });
    });

    function applyFontSize(size) {
        if (size === 'medium') {
            document.documentElement.removeAttribute('data-font-size');
        } else {
            document.documentElement.setAttribute('data-font-size', size);
        }
        localStorage.setItem('societyos-font-size', size);
    }
    document.querySelectorAll('.font-size-option').forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyFontSize(btn.getAttribute('data-font-size-value'));
        });
    });
})();
</script>
</body>
</html>
