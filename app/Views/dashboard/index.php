<?php
$pageTitle = 'Dashboard';
ob_start();
?>
<div class="dashboard-page" style="display:block;min-height:200px;color:var(--bs-body-color);">
<h3 class="mb-4">Welcome, <?= htmlspecialchars($userName) ?> <span class="badge bg-secondary"><?= htmlspecialchars($roleName ?? '') ?></span></h3>

<div class="row g-3">
    <div class="col-md-3">
        <a href="/society/wings" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View flats and wings">
        <div class="card border-0 shadow-sm kpi-card kpi-primary h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-door-open"></i></div>
                <div>
                    <div class="text-muted small">Total Flats</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['flats'] ?></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="/members" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View active residents">
        <div class="card border-0 shadow-sm kpi-card kpi-info h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="text-muted small">Active Residents</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['active_members'] ?></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="/visitors" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View today's visitors">
        <div class="card border-0 shadow-sm kpi-card kpi-secondary h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-id-card"></i></div>
                <div>
                    <div class="text-muted small">Today's Visitors</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['visitors_today'] ?></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="/complaints" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View pending complaints">
        <div class="card border-0 shadow-sm kpi-card kpi-warning h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="text-muted small">Pending Complaints</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['pending_complaints'] ?></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/billing/defaulters" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View outstanding dues">
        <div class="card border-0 shadow-sm kpi-card kpi-danger h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="text-muted small">Outstanding Dues</div>
                    <div class="fs-3 fw-bold text-danger"><?= number_format((float) $stats['outstanding_amount'], 2) ?></div>
                    <?php if ($stats['defaulter_count'] > 0): ?>
                        <div class="small text-muted"><?= (int) $stats['defaulter_count'] ?> flat<?= $stats['defaulter_count'] > 1 ? 's' : '' ?> overdue</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/accounting/income-details" class="text-decoration-none text-reset d-block h-100" aria-label="View income calculation details">
            <div class="card border-0 shadow-sm kpi-card kpi-success h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                    <div class="flex-grow-1">
                        <div class="text-muted small">Income (This Month)</div>
                        <div class="fs-3 fw-bold text-success"><?= number_format((float) $stats['income_this_month'], 2) ?></div>
                        <span class="small text-success"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View calculation details</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/accounting/expenses" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View expenses">
        <div class="card border-0 shadow-sm kpi-card kpi-warning h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-arrow-trend-down"></i></div>
                <div>
                    <div class="text-muted small">Expenses (This Month)</div>
                    <div class="fs-3 fw-bold text-warning"><?= number_format((float) $stats['expenses_this_month'], 2) ?></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/accounting/accounts" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="View account balances">
        <div class="card border-0 shadow-sm kpi-card kpi-primary h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                <div>
                    <div class="text-muted small">Total Account Balance</div>
                    <div class="fs-3 fw-bold"><?= number_format((float) $stats['account_balance'], 2) ?></div>
                    <div class="small dashboard-card-hint">View accounts <i class="fa-solid fa-arrow-right ms-1"></i></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/vehicles/parking" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="Manage parking">
        <div class="card border-0 shadow-sm kpi-card kpi-info h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-car"></i></div>
                <div>
                    <div class="text-muted small">Parking Occupancy</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['parking_occupied'] ?> / <?= (int) $stats['parking_total'] ?></div>
                    <div class="small dashboard-card-hint">Manage parking <i class="fa-solid fa-arrow-right ms-1"></i></div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/staff" class="dashboard-card-link text-decoration-none text-reset d-block h-100" aria-label="Review staff verification">
        <div class="card border-0 shadow-sm kpi-card <?= $stats['staff_verification_pending'] > 0 ? 'kpi-warning' : 'kpi-secondary' ?> h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div>
                    <div class="text-muted small">Staff Verification Pending</div>
                    <div class="fs-3 fw-bold <?= $stats['staff_verification_pending'] > 0 ? 'text-warning' : '' ?>"><?= (int) $stats['staff_verification_pending'] ?></div>
                    <?php if ($stats['staff_verification_pending'] > 0): ?>
                        <div class="small dashboard-card-hint">Review staff <i class="fa-solid fa-arrow-right ms-1"></i></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <h6 class="mb-3">Contracts &amp; Warranties Expiring in the Next 30 Days</h6>
        <?php
        $expiringItems = [];
        foreach ($expiringAmc as $amc) {
            $expiringItems[] = [
                'type' => 'AMC',
                'asset_id' => $amc['asset_id'],
                'label' => $amc['asset_name'],
                'detail' => $amc['vendor_name'] ?: '-',
                'expiry' => $amc['end_date'],
            ];
        }
        foreach ($expiringWarranties as $warranty) {
            $expiringItems[] = [
                'type' => 'Warranty',
                'asset_id' => $warranty['id'],
                'label' => $warranty['name'],
                'detail' => $warranty['category_name'],
                'expiry' => $warranty['warranty_expiry'],
            ];
        }
        usort($expiringItems, fn ($a, $b) => $a['expiry'] <=> $b['expiry']);
        ?>
        <table class="table table-sm mb-0">
            <thead><tr><th>Type</th><th>Asset</th><th>Vendor / Category</th><th>Expires</th><th>Days Left</th></tr></thead>
            <tbody>
            <?php foreach ($expiringItems as $item): ?>
                <?php $daysLeft = (int) ((strtotime($item['expiry']) - strtotime('today')) / 86400); ?>
                <tr>
                    <td><span class="badge bg-<?= $item['type'] === 'AMC' ? 'info' : 'secondary' ?>"><?= $item['type'] ?></span></td>
                    <td><a href="/assets/<?= (int) $item['asset_id'] ?>"><?= htmlspecialchars($item['label']) ?></a></td>
                    <td><?= htmlspecialchars($item['detail']) ?></td>
                    <td><?= htmlspecialchars($item['expiry']) ?></td>
                    <td><span class="badge bg-<?= $daysLeft <= 7 ? 'danger' : 'warning text-dark' ?>"><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($expiringItems)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Nothing expiring in the next 30 days.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </a>
    </div>
</div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
