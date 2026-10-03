<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($society['name'] ?? 'SocietyOS') ?> &mdash; SocietyOS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
<style>
    body { background: #f5f6fa; }
    .hero {
        background: linear-gradient(135deg, #1a1d29 0%, #2c3454 100%);
        color: #fff;
        padding: 5rem 1rem 6rem;
    }
    .hero .badge-society {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
    }
    .feature-card {
        border: 0;
        border-radius: 0.9rem;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .feature-card:hover { transform: translateY(-4px); box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.08); }
    .feature-icon {
        width: 48px; height: 48px;
        border-radius: 0.75rem;
        display: flex; align-items: center; justify-content: center;
        background: #eef1ff; color: #3b4ba0;
        font-size: 1.1rem;
    }
    .role-pill {
        border: 1px solid #dfe3ee; border-radius: 2rem; padding: .35rem .9rem;
        font-size: .85rem; color: #495066; background: #fff;
    }
</style>
</head>
<body>

<nav class="navbar navbar-dark px-4 py-3" style="background:#161925;">
    <span class="navbar-brand mb-0"><i class="fa-solid fa-building me-2"></i>SocietyOS</span>
    <a href="/login" class="btn btn-outline-light btn-sm">Login <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i></a>
</nav>

<header class="hero text-center">
    <span class="badge badge-society rounded-pill px-3 py-2 mb-3">
        <i class="fa-solid fa-house-flag me-1"></i><?= htmlspecialchars($society['name'] ?? 'Your Residential Society') ?>
    </span>
    <h1 class="display-5 fw-bold mb-3">One portal for everything your society runs on</h1>
    <p class="lead mb-4" style="max-width: 640px; margin-inline: auto; opacity: .85;">
        Residents, billing, accounting, visitors, complaints, staff, and assets &mdash;
        managed from a single, secure, role-based system built for this society.
    </p>
    <a href="/login" class="btn btn-light btn-lg px-4 me-2">
        <i class="fa-solid fa-right-to-bracket me-2"></i>Login to the Portal
    </a>
</header>

<main class="container my-5">
    <div class="text-center mb-5">
        <h2 class="h4 fw-bold">Everything the committee, staff, and residents need</h2>
        <p class="text-muted">Each module is access-controlled by role &mdash; residents see their own data, staff see their tasks, admins see it all.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card feature-card shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="fa-solid fa-users"></i></div>
                    <h6 class="fw-bold">Resident Management</h6>
                    <p class="text-muted small mb-0">Owners, tenants, family members, and emergency contacts, tied to each flat.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <h6 class="fw-bold">Maintenance Billing</h6>
                    <p class="text-muted small mb-0">Bulk bill generation, payment collection, receipts, and defaulter tracking.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h6 class="fw-bold">Accounting</h6>
                    <p class="text-muted small mb-0">Income, expenses, vendors, and ledger &mdash; full financial visibility.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="fa-solid fa-id-card"></i></div>
                    <h6 class="fw-bold">Visitors &amp; Security</h6>
                    <p class="text-muted small mb-0">Visitor logs, QR passes, and delivery register at the gate.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h6 class="fw-bold">Complaints</h6>
                    <p class="text-muted small mb-0">Raise, assign, and track resolution &mdash; nothing falls through the cracks.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card shadow-sm h-100">
                <div class="card-body">
                    <div class="feature-icon mb-3"><i class="fa-solid fa-user-tie"></i></div>
                    <h6 class="fw-bold">Staff &amp; Assets</h6>
                    <p class="text-muted small mb-0">Attendance, payroll, and asset/AMC tracking for society-owned equipment.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-muted small mb-2">Access is role-based:</p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <span class="role-pill"><i class="fa-solid fa-user-shield me-1"></i>Society Admin</span>
            <span class="role-pill"><i class="fa-solid fa-calculator me-1"></i>Accountant</span>
            <span class="role-pill"><i class="fa-solid fa-people-group me-1"></i>Committee Member</span>
            <span class="role-pill"><i class="fa-solid fa-shield-halved me-1"></i>Security Guard</span>
            <span class="role-pill"><i class="fa-solid fa-house-user me-1"></i>Resident / Tenant</span>
        </div>
    </div>
</main>

<footer class="text-center py-4 text-muted small">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($society['name'] ?? 'SocietyOS') ?>. Powered by SocietyOS.
</footer>

</body>
</html>
