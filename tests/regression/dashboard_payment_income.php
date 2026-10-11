<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$society = file_get_contents($root . '/app/Models/Society.php');
$paymentProofController = file_get_contents($root . '/app/Controllers/PaymentProofController.php');
$billingService = file_get_contents($root . '/app/Services/BillingService.php');
$accountingController = file_get_contents($root . '/app/Controllers/AccountingController.php');
$dashboardView = file_get_contents($root . '/app/Views/dashboard/index.php');
$incomeDetailsView = file_get_contents($root . '/app/Views/accounting/income_details.php');
$routes = file_get_contents($root . '/public/index.php');
$accountingService = file_get_contents($root . '/app/Services/AccountingService.php');

$checks = [
    'dashboard retains manually recorded income' => str_contains($society, 'FROM income i'),
    'dashboard includes recorded maintenance payments' => str_contains($society, 'FROM payments p'),
    'payment totals are scoped through the bill society' => str_contains($society, 'JOIN maintenance_bills b ON b.id = p.maintenance_bill_id') && str_contains($society, 'WHERE b.society_id = :payment_sid'),
    'payment totals use payment date for the current month' => str_contains($society, 'MONTH(p.paid_at) = MONTH(CURDATE())') && str_contains($society, 'YEAR(p.paid_at) = YEAR(CURDATE())'),
    'payment proofs count only after approval records a payment' => str_contains($paymentProofController, "if (\$decision==='approve')") && str_contains($paymentProofController, 'BillingService::recordPayment'),
    'payment recording commits before being treated as confirmed' => str_contains($billingService, 'Payment::create(') && str_contains($billingService, '$pdo->commit();'),
    'dashboard income KPI links to its calculation details' => str_contains($dashboardView, 'href="/accounting/income-details"'),
    'income details route requires accounting view permission' => str_contains($routes, "'/accounting/income-details'") && str_contains($routes, "'incomeDetails'") && str_contains($routes, "'accounting.view'"),
    'income detail calculation includes manual entries and confirmed payments' => str_contains($accountingController, 'public function incomeDetails(): void') && str_contains($accountingController, 'FROM income i') && str_contains($accountingController, 'FROM payments p') && str_contains($accountingController, '$totalIncome = $manualTotal + $paymentTotal;'),
    'income details explain pending proofs are excluded' => str_contains($incomeDetailsView, 'Pending payment proofs are excluded until approved.'),
    'manual income rows expose edit and delete actions' => str_contains($incomeDetailsView, '/accounting/income/<?= (int) $row[\'income_id\'] ?>/edit') && str_contains($incomeDetailsView, '/delete'),
    'confirmed maintenance payments link to payment management' => str_contains($incomeDetailsView, 'href="/billing/payment-proofs"') && str_contains($incomeDetailsView, 'Managed in Payments'),
    'manual income CRUD routes require accounting manage permission' => str_contains($routes, "'/accounting/income/{id}/edit'") && str_contains($routes, "'/accounting/income/{id}'") && str_contains($routes, "'/accounting/income/{id}/delete'") && str_contains($routes, "'accounting.manage'"),
    'manual income edits and deletes maintain linked ledger entries' => str_contains($accountingController, 'AccountingService::updateIncome') && str_contains($accountingController, 'AccountingService::deleteIncome') && str_contains($accountingService, 'reference_type = "income"'),

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
