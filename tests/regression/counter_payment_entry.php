<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routes = file_get_contents($root . '/public/index.php');
$controller = file_get_contents($root . '/app/Controllers/BillingController.php');
$model = file_get_contents($root . '/app/Models/MaintenanceBill.php');
$form = file_get_contents($root . '/app/Views/billing/receive_payment.php');
$menu = file_get_contents($root . '/app/Views/layouts/app.php');
$billingIndex = file_get_contents($root . '/app/Views/billing/index.php');
$seed = file_get_contents($root . '/database/seed.sql');

$checks = [
    'payment entry is protected by billing manage permission' => str_contains($routes, "'/billing/receive-payment'") && str_contains($routes, "'billing.manage'"),
    'counter payment uses the existing atomic payment and receipt service' => str_contains($controller, 'BillingService::recordPayment(') && str_contains($controller, "Flash::set('success', \"Payment received."),
    'payment entry lists only society-scoped outstanding bills with active resident links' => str_contains($model, 'outstandingForSociety(int $societyId)') && str_contains($model, 'b.status <> \'paid\'') && str_contains($model, 'b.paid_amount < b.total_amount') && str_contains($model, 'active_member.status = \'active\'') && str_contains($model, 'AND EXISTS ('),
    'payment form captures bill amount and payment mode with CSRF' => str_contains($form, "Csrf::field()") && str_contains($form, 'name="bill_id"') && str_contains($form, 'name="amount"') && str_contains($form, 'name="payment_mode"'),
    'accountant and society admin roles have payment permission' => str_contains($seed, "'society_admin'") && str_contains($seed, "'accountant'") && str_contains($seed, "'billing.manage'"),
    'payment entry is discoverable from billing navigation' => str_contains($menu, "'href'=>'/billing/receive-payment'") && str_contains($billingIndex, 'href="/billing/receive-payment"'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Counter payment entry regression detected.\\n");
    exit(1);
}
echo "Counter payment entry regression contract passed.\\n";
