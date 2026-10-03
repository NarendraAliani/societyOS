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
                <li class="nav-item"><a class="nav-link text-white" href="/resident/vehicles"><i class="fa-solid fa-car me-2"></i>My Vehicles</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="/resident/documents"><i class="fa-solid fa-folder-open me-2"></i>My Documents</a></li>
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
                <li class="nav-item"><a class="nav-link text-white" href="/admin/integrations"><i class="fa-solid fa-plug me-2"></i>Integrations</a></li>
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
                        <li><hr class="dropdown-divider"></li>
                        <li><form method="post" action="/logout" class="m-0"><?= \App\Helpers\Csrf::field() ?><button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</button></form></li>
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

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.dataset.skipConfirm === 'true') return;
        var action = (form.getAttribute('action') || '').toLowerCase();
        if (/\/(delete|restore|remove|reset-password)(?:\/|$)/.test(action)) {
            var message;
            if (action.indexOf('/restore') !== -1) {
                message = 'Restore this backup? The current database will be replaced after a safety backup.';
            } else if (action.indexOf('/reset-password') !== -1) {
                message = 'Reset this user password? Their current password will stop working and they will be required to change the new password.';
            } else if (action.indexOf('/remove') !== -1) {
                message = 'Remove this role assignment? This will unlink the selected role from the user account.';
            } else {
                message = 'Delete this item? This action cannot be undone.';
            }
            if (!window.confirm(message)) event.preventDefault();
        }
    });

    // Add lightweight client-side search to long tables. It is deliberately opt-in by row count
    // so small action tables and forms remain visually unchanged.
    document.querySelectorAll('#app-content table').forEach(function (table) {
        var body = table.tBodies && table.tBodies[0];
        if (!body || body.rows.length < 10 || table.dataset.noSearch === 'true') return;
        var wrapper = table.parentElement;
        if (wrapper && wrapper.previousElementSibling && wrapper.previousElementSibling.classList.contains('societyos-table-search')) return;
        var box = document.createElement('div');
        box.className = 'societyos-table-search mb-2';
        box.innerHTML = '<label class="visually-hidden">Search table</label><input type="search" class="form-control form-control-sm" placeholder="Search this table...">';
        var input = box.querySelector('input');
        input.addEventListener('input', function () {
            var term = input.value.toLowerCase().trim();
            Array.prototype.forEach.call(body.rows, function (row) {
                row.hidden = term !== '' && row.textContent.toLowerCase().indexOf(term) === -1;
            });
        });
        if (wrapper) wrapper.parentNode.insertBefore(box, wrapper);
    });

    // Keep wide data tables usable on phones without requiring every view to hand-wrap them.
    document.querySelectorAll('#app-content table').forEach(function (table) {
        if (table.closest('.table-responsive')) return;
        var wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });

    // Any table-row edit/management form that was historically embedded in a
    // Bootstrap collapse is presented as a modal instead. This keeps list pages
    // compact and makes the interaction consistent across the application.
    (function promoteEmbeddedCollapseFormsToModals() {
        var counter = 0;
        document.querySelectorAll('[data-bs-toggle="collapse"][data-bs-target^="#"]').forEach(function (trigger) {
            var selector = trigger.getAttribute('data-bs-target');
            if (!selector) return;
            var target;
            try { target = document.querySelector(selector); } catch (e) { return; }
            if (!target || !target.querySelector('form[method="post"], form[method="POST"]')) return;

            counter += 1;
            var modalId = 'societyos-edit-modal-' + counter;
            var modal = document.createElement('div');
            modal.className = 'modal fade app-form-modal';
            modal.id = modalId;
            modal.tabIndex = -1;
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = '<div class="modal-dialog modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit / Manage</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"></div></div></div>';
            var modalBody = modal.querySelector('.modal-body');

            // Choose a wider dialog for multi-field management forms so labels and
            // controls have room to breathe instead of collapsing into narrow columns.
            var sourceForm = target.querySelector('form[method="post"], form[method="POST"]');
            if (sourceForm) {
                var gridFields = sourceForm.querySelectorAll(':scope > [class*="col-"]');
                if (gridFields.length >= 5) {
                    modal.querySelector('.modal-dialog').classList.add('modal-xl');
                } else if (gridFields.length >= 3) {
                    modal.querySelector('.modal-dialog').classList.add('modal-lg');
                }
            }

            // Move the live form nodes instead of cloning them. Cloning would drop event
            // listeners installed by a page (for example dependent role/home dropdowns).
            // Also unwrap table rows: a <tr> directly inside a modal body is invalid markup
            // and can make the management panel appear empty/non-interactive.
            var source = target;
            if (target.tagName === 'TR') {
                source = target.querySelector('td') || target;
            }
            while (source.firstChild) {
                modalBody.appendChild(source.firstChild);
            }

            document.body.appendChild(modal);
            target.remove();

            // Bootstrap's delegated data API is not used here because the trigger originally
            // targeted a collapse that has now been removed. Explicitly bind the modal instance
            // so every converted Edit/Manage button remains reliably clickable.
            var modalInstance = new bootstrap.Modal(modal);
            trigger.removeAttribute('data-bs-toggle');
            trigger.removeAttribute('data-bs-target');
            trigger.removeAttribute('aria-expanded');
            trigger.removeAttribute('aria-controls');
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                modalInstance.show();
            });
        });
    })();
})();
</script>
</body>
</html>
