<?php
declare(strict_types=1);

/**
 * Regression contract for the global linked-home rule.
 *
 * This is intentionally a source-level contract because the CI environment
 * does not have the production SocietyOS database. It catches the exact class
 * of regression that caused the linked-home selector to stop loading for
 * non-resident roles.
 */

$files = [
    'app/Views/admin/create_user.php',
    'app/Controllers/AdminController.php',
    'app/Models/User.php',
];

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing required file: {$file}\n");
        exit(1);
    }
}

$view = file_get_contents('app/Views/admin/create_user.php');
$controller = file_get_contents('app/Controllers/AdminController.php');
$model = file_get_contents('app/Models/User.php');

$checks = [
    'create-user view calls the role-scoped candidate endpoint' =>
        str_contains($view, '/admin/users/resident-candidates?role_id='),
    'create-user view does not restrict AJAX loading to resident/tenant roles' =>
        !preg_match('/\[\s*[\'\"]resident[\'\"]\s*,\s*[\'\"]tenant[\'\"]\s*\]\s*\.includes\s*\(/', $view),
    'controller does not restrict candidate lookup to resident/tenant roles' =>
        !preg_match('/role[\s_-]*name.*(?:resident|tenant)|(?:resident|tenant).*role[\s_-]*name/i', $controller),
    'model eligibility does not restrict roles to resident/tenant' =>
        !preg_match('/memberEligibleForRole[\s\S]{0,250}(?:resident|tenant).*role/i', $model),
    'model excludes already-linked flats for the selected role' =>
        str_contains($model, 'linked_member.flat_id = m.flat_id'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    if (!$passed) {
        $failed = true;
    }
}

if ($failed) {
    fwrite(STDERR, "\nLinked-home role-scope regression detected.\n");
    exit(1);
}

echo "\nAll linked-home regression contracts passed.\n";
