<?php
declare(strict_types=1);

$files = [
    'app/Views/layouts/app.php',
    'app/Controllers/ResidentController.php',
    'app/Views/resident/family.php',
    'public/index.php',
];

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing required file: {$file}\n");
        exit(1);
    }
}

$layout = file_get_contents('app/Views/layouts/app.php');
$controller = file_get_contents('app/Controllers/ResidentController.php');
$view = file_get_contents('app/Views/resident/family.php');
$routes = file_get_contents('public/index.php');

$checks = [
    'resident sidebar exposes My Family' => str_contains($layout, 'href="/resident/family"') && str_contains($layout, '>My Family</a>'),
    'logout has one dedicated topbar form' => substr_count($layout, 'action="/logout"') === 1,
    'family page is resident-scoped' => str_contains($controller, 'public function family(): void') && str_contains($controller, '$member = $this->member();'),
    'family add uses current member' => str_contains($controller, 'FamilyMember::create((int) $member'),
    'family delete checks ownership' => str_contains($controller, '$familyMember') && str_contains($controller, 'Auth::memberId()'),
    'emergency contact delete checks ownership' => str_contains($controller, '$contact') && str_contains($controller, 'Auth::memberId()'),
    'family view supports add, edit and remove' => str_contains($view, 'Add Family Member') && str_contains($view, 'Edit Family Member') && str_contains($view, '/resident/family-members/'),
    'family forms use shared modal pattern' => str_contains($view, 'app-form-modal') && str_contains($view, 'data-bs-toggle="modal"'),
    'family edit preserves ownership checks' => str_contains($controller, 'FamilyMember::update') && str_contains($controller, 'familyMember') && str_contains($controller, 'Auth::memberId()'),
    'emergency edit preserves ownership checks' => str_contains($controller, 'EmergencyContact::update') && str_contains($controller, 'contact') && str_contains($controller, 'Auth::memberId()'),
    'modal standard is documented' => is_file('docs/UI_FORM_STANDARD.md'),
    'family routes exist' => str_contains($routes, "ResidentController::class, 'family'") && str_contains($routes, "/resident/family-members"),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    if (!$passed) {
        $failed = true;
    }
}

if ($failed) {
    fwrite(STDERR, "\nResident navigation regression detected.\n");
    exit(1);
}

echo "\nAll resident navigation regression contracts passed.\n";
