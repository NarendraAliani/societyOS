<?php
declare(strict_types=1);

$files = [
    'app/Views/layouts/app.php',
    'app/Controllers/ResidentController.php',
    'app/Views/resident/family.php',
    'app/Views/members/show.php',
    'app/Controllers/MemberController.php',
    'app/Controllers/VehicleController.php',
    'app/Models/Document.php',
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
$memberView = file_get_contents('app/Views/members/show.php');
$memberController = file_get_contents('app/Controllers/MemberController.php');
$vehicleController = file_get_contents('app/Controllers/VehicleController.php');
$documentModel = file_get_contents('app/Models/Document.php');

$checks = [
    'resident sidebar exposes My Family' => str_contains($layout, 'href="/resident/family"') && str_contains($layout, '>My Family</a>'),
    'logout is not rendered in the shared topbar' => !str_contains($layout, 'action="/logout"'),
    'responsive sidebar toggle exists' => str_contains($layout, 'app-sidebar-toggle') && str_contains($layout, 'id="app-sidebar"'),
    'responsive shell styles exist' => str_contains(file_get_contents('public/static/css/app.css'), '@media (max-width: 991.98px)') && str_contains(file_get_contents('public/static/css/app.css'), 'sidebar-open'),
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
    'member detail uses modal-first forms' => str_contains($memberView, 'app-form-modal') && str_contains($memberView, 'Edit Resident') && str_contains($memberView, 'Upload Document'),
    'member detail supports family and contact edits' => str_contains($memberController, 'FamilyMember::update') && str_contains($memberController, 'EmergencyContact::update'),
    'member detail supports vehicle add and edit modals' => str_contains($memberView, 'id="addVehicleModal"') && str_contains($memberView, 'Edit Vehicle') && str_contains($vehicleController, '$returnToMember'),
    'member detail documents are society-scoped' => str_contains($documentModel, 'member_society_id') && str_contains($memberController, 'document[\'member_society_id\']'),
    'member detail lease uses modal' => str_contains($memberView, 'id="leaseModal"') && str_contains($memberView, 'agreement_doc'),
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
