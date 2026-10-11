<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$layout = file_get_contents($root . '/app/Views/layouts/app.php');
$view = file_get_contents($root . '/app/Views/accounting/expenses.php');
$routes = file_get_contents($root . '/public/index.php');

$checks = [
    'left navigation exposes Add Expenses for users with accounting manage permission' => str_contains($layout, 'Auth::can') && str_contains($layout, "'permission'=>'accounting.manage'") && str_contains($layout, "'href'=>'/accounting/expenses'") && str_contains($layout, "'label'=>'Add Expenses'"),
    'expense page allows adding a category while recording an expense' => str_contains($view, 'name="category"') && str_contains($view, 'Category *'),
    'expense entry route remains protected by accounting manage permission' => str_contains($routes, "'/accounting/expenses'") && str_contains($routes, "'accounting.manage'"),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Add Expenses navigation regression detected.\n");
    exit(1);
}
echo "Add Expenses navigation regression contract passed.\n";
