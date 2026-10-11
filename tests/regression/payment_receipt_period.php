<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$model = file_get_contents($root . '/app/Models/Receipt.php');
$view = file_get_contents($root . '/app/Views/billing/receipt_pdf.php');

$checks = [
    'receipt detail query loads bill period start and end' => str_contains($model, 'b.bill_period_start, b.bill_period_end'),
    'receipt displays the maintenance bill period' => str_contains($view, '<td>Bill Period</td>') && str_contains($view, "$detail['bill_period_start']") && str_contains($view, "$detail['bill_period_end']"),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Payment receipt period regression detected.\\n");
    exit(1);
}
echo "Payment receipt period regression contract passed.\\n";
