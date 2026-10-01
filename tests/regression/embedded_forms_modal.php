<?php

declare(strict_types=1);

$layout = file_get_contents(__DIR__ . '/../../app/Views/layouts/app.php');
if (strpos($layout, 'promoteEmbeddedCollapseFormsToModals') === false ||
    strpos($layout, 'data-bs-toggle\', \'modal\'') === false) {
    fwrite(STDERR, "Global embedded-form modal behavior is missing.\n");
    exit(1);
}

$modalViews = [
    'app/Views/visitors/index.php',
    'app/Views/visitors/deliveries.php',
    'app/Views/visitors/passes.php',
    'app/Views/notices/index.php',
    'app/Views/notices/events.php',
    'app/Views/notices/polls.php',
    'app/Views/assets/categories.php',
    'app/Views/staff/leave.php',
    'app/Views/society/wings.php',
    'app/Views/society/maintenance_heads.php',
    'app/Views/society/maintenance_head_detail.php',
    'app/Views/accounting/accounts.php',
    'app/Views/accounting/vendors.php',
    'app/Views/accounting/income.php',
    'app/Views/accounting/expenses.php',
    'app/Views/vehicles/parking.php',
    'app/Views/vehicles/parking_rates.php',
    'app/Views/complaints/categories.php',
];

foreach ($modalViews as $view) {
    $path = __DIR__ . '/../../' . $view;
    $source = file_get_contents($path);
    if (strpos($source, 'data-bs-toggle="modal"') === false) {
        fwrite(STDERR, "Expected modal trigger missing: {$view}\n");
        exit(1);
    }
}

echo "Embedded-form modal regression contract passed.\n";
