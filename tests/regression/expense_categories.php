<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$model = file_get_contents($root . '/app/Models/Expense.php');
$controller = file_get_contents($root . '/app/Controllers/AccountingController.php');
$view = file_get_contents($root . '/app/Views/accounting/expenses.php');
$routes = file_get_contents($root . '/public/index.php');

$checks = [
    'category dropdown is populated from society-specific saved and legacy categories' => str_contains($model, 'categoriesForSociety(int $societyId)') && str_contains($model, 'Settings::get(' . '$societyId, \'expense_categories\'') && str_contains($model, 'SELECT DISTINCT category FROM expenses'),
    'record expense uses a dropdown and offers add category in the modal' => str_contains($view, '<select name="category"') && str_contains($view, 'modal-expense-category-add') && str_contains($view, 'Add Expense Category'),
    'new categories are saved with society-scoped settings and duplicate names rejected' => str_contains($model, 'Settings::set(' . '$societyId, \'expense_categories\'') && str_contains($model, 'mb_strtolower($category) === mb_strtolower($name)'),
    'category creation route requires accounting manage permission' => str_contains($routes, "'/accounting/expense-categories'") && str_contains($routes, "'accounting.manage'"),
    'expense submission validates the selected category against saved options' => str_contains($controller, 'in_array($category, Expense::categoriesForSociety(Society::currentId()), true)'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Expense category regression detected.\n");
    exit(1);
}
echo "Expense category regression contract passed.\n";
