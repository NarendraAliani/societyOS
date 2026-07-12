<?php
$pageTitle = 'Reports';
ob_start();

$reports = [
    ['title' => 'Collection Report', 'href' => '/reports/collection', 'icon' => 'fa-file-invoice-dollar', 'desc' => 'Maintenance payments received in a date range.'],
    ['title' => 'Defaulter Report', 'href' => '/reports/defaulters', 'icon' => 'fa-triangle-exclamation', 'desc' => 'Overdue bills and outstanding amounts.'],
    ['title' => 'Income Report', 'href' => '/reports/income', 'icon' => 'fa-arrow-trend-up', 'desc' => 'All income entries in a date range.'],
    ['title' => 'Expense Report', 'href' => '/reports/expense', 'icon' => 'fa-arrow-trend-down', 'desc' => 'All expense entries in a date range.'],
    ['title' => 'Visitor Report', 'href' => '/reports/visitors', 'icon' => 'fa-id-card', 'desc' => 'Gate visitor log in a date range.'],
    ['title' => 'Complaint Report', 'href' => '/reports/complaints', 'icon' => 'fa-clipboard-list', 'desc' => 'Complaints summarized by category and status.'],
    ['title' => 'Staff Report', 'href' => '/reports/staff', 'icon' => 'fa-user-tie', 'desc' => 'Staff roster and status.'],
    ['title' => 'Asset Report', 'href' => '/reports/assets', 'icon' => 'fa-toolbox', 'desc' => 'Asset register with cost and warranty.'],
    ['title' => 'Occupancy Report', 'href' => '/reports/occupancy', 'icon' => 'fa-door-open', 'desc' => 'Flat occupancy status society-wide.'],
    ['title' => 'Parking Report', 'href' => '/reports/parking', 'icon' => 'fa-square-parking', 'desc' => 'Parking slot allocation status.'],
];
?>
<div class="row g-3">
    <?php foreach ($reports as $report): ?>
        <div class="col-md-4">
            <a href="<?= $report['href'] ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fa-solid <?= $report['icon'] ?> fa-lg text-primary mb-2"></i>
                        <h6 class="text-dark"><?= $report['title'] ?></h6>
                        <p class="text-muted small mb-0"><?= $report['desc'] ?></p>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
