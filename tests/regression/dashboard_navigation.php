<?php
declare(strict_types=1);

$layout = file_get_contents(__DIR__ . '/../../app/Views/layouts/app.php');
$adminDashboard = file_get_contents(__DIR__ . '/../../app/Views/dashboard/index.php');
$residentDashboard = file_get_contents(__DIR__ . '/../../app/Views/resident/home.php');
$css = file_get_contents(__DIR__ . '/../../public/static/css/app.css');

$checks = [
    'sidebar groups admin navigation by workflow' => str_contains($layout, 'PEOPLE &amp; SERVICES') && str_contains($layout, 'FINANCE') && str_contains($layout, 'MANAGEMENT'),
    'resident sidebar stays self-service focused' => str_contains($layout, 'MY SOCIETY') && str_contains($layout, "'label'=>'My Family'") && str_contains($layout, "'label'=>'My Vehicles'"),
    'admin navigation is permission filtered' => str_contains($layout, 'Auth::can') && str_contains($layout, "'permission'=>'accounting.manage'") && str_contains($layout, "'permission'=>'users.manage'"),
    'sidebar highlights the active route accessibly' => str_contains($layout, 'aria-current="page"') && str_contains($layout, '$isNavActive'),
    'toolbar retains role switching and account actions' => str_contains($layout, 'switch-role') && str_contains($layout, 'Change Password') && str_contains($layout, '>Logout</button>'),
    'admin KPI cards remain direct navigation targets' => str_contains($adminDashboard, 'dashboard-card-link') && str_contains($adminDashboard, '/accounting/income-details') && str_contains($adminDashboard, '/vehicles/parking'),
    'resident summary cards are fully clickable' => str_contains($residentDashboard, 'resident-summary-link') && substr_count($residentDashboard, 'class="resident-summary-link"') === 3,
    'responsive dashboard and sidebar styling exists' => str_contains($css, '.sidebar-section-label') && str_contains($css, '.resident-dashboard-header') && str_contains($css, '@media(max-width:575.98px)'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    if (!$passed) $failed = true;
}
if ($failed) exit(1);
echo "Dashboard and navigation regression contract passed.\n";
