<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$society = file_get_contents($root . '/app/Models/Society.php');
$paymentProofController = file_get_contents($root . '/app/Controllers/PaymentProofController.php');
$billingService = file_get_contents($root . '/app/Services/BillingService.php');

$checks = [
    'dashboard retains manually recorded income' => str_contains($society, 'FROM income i'),
    'dashboard includes recorded maintenance payments' => str_contains($society, 'FROM payments p'),
    'payment totals are scoped through the bill society' => str_contains($society, 'JOIN maintenance_bills b ON b.id = p.maintenance_bill_id') && str_contains($society, 'WHERE b.society_id = :payment_sid'),
    'payment totals use payment date for the current month' => str_contains($society, 'MONTH(p.paid_at) = MONTH(CURDATE())') && str_contains($society, 'YEAR(p.paid_at) = YEAR(CURDATE())'),
    'payment proofs count only after approval records a payment' => str_contains($paymentProofController, "if (\$decision==='approve')") && str_contains($paymentProofController, 'BillingService::recordPayment'),
    'payment recording commits before being treated as confirmed' => str_contains($billingService, 'Payment::create(') && str_contains($billingService, '$pdo->commit();'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}

if ($failed) {
    fwrite(STDERR, "\nDashboard payment-income regression detected.\n");
    exit(1);
}

echo "Dashboard payment-income regression contract passed.\n";
