<?php
$pageTitle = 'Dashboard';
ob_start();
?>
<h3 class="mb-4">Welcome, <?= htmlspecialchars($userName) ?> <span class="badge bg-secondary"><?= htmlspecialchars($roleName ?? '') ?></span></h3>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm kpi-card kpi-primary">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-door-open"></i></div>
                <div>
                    <div class="text-muted small">Total Flats</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['flats'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm kpi-card kpi-info">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="text-muted small">Active Residents</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['active_members'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm kpi-card kpi-secondary">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-id-card"></i></div>
                <div>
                    <div class="text-muted small">Today's Visitors</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['visitors_today'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm kpi-card kpi-warning">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="text-muted small">Pending Complaints</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['pending_complaints'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm kpi-card kpi-danger">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="text-muted small">Outstanding Dues</div>
                    <div class="fs-3 fw-bold text-danger"><?= number_format((float) $stats['outstanding_amount'], 2) ?></div>
                    <?php if ($stats['defaulter_count'] > 0): ?>
                        <a href="/billing/defaulters" class="small text-decoration-none"><?= (int) $stats['defaulter_count'] ?> flat<?= $stats['defaulter_count'] > 1 ? 's' : '' ?> overdue</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm kpi-card kpi-success">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div>
                    <div class="text-muted small">Income (This Month)</div>
                    <div class="fs-3 fw-bold text-success"><?= number_format((float) $stats['income_this_month'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm kpi-card kpi-warning">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-arrow-trend-down"></i></div>
                <div>
                    <div class="text-muted small">Expenses (This Month)</div>
                    <div class="fs-3 fw-bold text-warning"><?= number_format((float) $stats['expenses_this_month'], 2) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm kpi-card kpi-primary">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                <div>
                    <div class="text-muted small">Total Account Balance</div>
                    <div class="fs-3 fw-bold"><?= number_format((float) $stats['account_balance'], 2) ?></div>
                    <a href="/accounting/accounts" class="small text-decoration-none">View accounts</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm kpi-card kpi-info">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-car"></i></div>
                <div>
                    <div class="text-muted small">Parking Occupancy</div>
                    <div class="fs-3 fw-bold"><?= (int) $stats['parking_occupied'] ?> / <?= (int) $stats['parking_total'] ?></div>
                    <a href="/vehicles/parking" class="small text-decoration-none">Manage parking</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm kpi-card <?= $stats['staff_verification_pending'] > 0 ? 'kpi-warning' : 'kpi-secondary' ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div>
                    <div class="text-muted small">Staff Verification Pending</div>
                    <div class="fs-3 fw-bold <?= $stats['staff_verification_pending'] > 0 ? 'text-warning' : '' ?>"><?= (int) $stats['staff_verification_pending'] ?></div>
                    <?php if ($stats['staff_verification_pending'] > 0): ?>
                        <a href="/staff" class="small text-decoration-none">Review staff</a>
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
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
