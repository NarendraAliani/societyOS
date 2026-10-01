<?php
declare(strict_types=1);

$files = [
    'app/Views/layouts/app.php',
    'app/Controllers/ResidentController.php',
    'app/Views/resident/family.php',
    'app/Views/resident/documents.php',
    'app/Views/resident/vehicles.php',
    'app/Views/members/show.php',
    'app/Controllers/MemberController.php',
    'app/Controllers/VehicleController.php',
    'app/Models/Document.php',
    'app/Models/Member.php',
    'app/Models/Tenant.php',
    'app/Models/Vehicle.php',
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
$documentsView = file_get_contents('app/Views/resident/documents.php');
$vehiclesView = file_get_contents('app/Views/resident/vehicles.php');
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
    'staff operations are society scoped' => str_contains(file_get_contents('app/Controllers/StaffController.php'), "Society::currentId()") && str_contains(file_get_contents('app/Models/Payroll.php'), 'belongsToStaff') && str_contains(file_get_contents('app/Models/LeaveRequest.php'), 'belongsToSociety'),

    'global shell has independent sidebar and content scrolling' => preg_match('/#app-shell\\s*\\{[^}]*height:\\s*100vh/s', file_get_contents('public/static/css/app.css')) && preg_match('/\\.sidebar\\s*\\{[^}]*height:\\s*100vh[^}]*overflow-y:\\s*auto/s', file_get_contents('public/static/css/app.css')) && preg_match('/#app-content\\s*\\{[^}]*overflow-y:\\s*auto/s', file_get_contents('public/static/css/app.css')),    'member detail sections use requested order' => strpos($memberView, '>Family Members</h5>') < strpos($memberView, '>Documents</h5>') && strpos($memberView, '>Vehicles</h5>') < strpos($memberView, '>Emergency Contacts</h5>'),
    'resident summary counts family members only' => str_contains(file_get_contents('app/Models/Member.php'), '(SELECT COUNT(*) FROM family_members fm WHERE fm.member_id = m.id) AS member_count') && !str_contains(file_get_contents('app/Models/Member.php'), '1 + (SELECT COUNT(*) FROM family_members fm WHERE fm.member_id = m.id) AS member_count'),
    'member detail supports family and contact edits' => str_contains($memberController, 'FamilyMember::update') && str_contains($memberController, 'EmergencyContact::update'),
    'member detail supports vehicle add and edit modals' => str_contains($memberView, 'id="addVehicleModal"') && str_contains($memberView, 'Edit Vehicle') && str_contains($vehicleController, '$returnToMember'),
    'member detail documents are society-scoped' => str_contains($documentModel, 'member_society_id') && str_contains($memberController, 'document[\'member_society_id\']'),
    'resident sidebar exposes documents' => str_contains($layout, 'href="/resident/documents"') && str_contains($layout, 'My Documents'),
    'resident sidebar exposes vehicles' => str_contains($layout, 'href="/resident/vehicles"') && str_contains($layout, 'My Vehicles'),
    'resident documents route exists' => str_contains($routes, "'/resident/documents'") && str_contains($controller, 'public function documents'),
    'resident vehicles route exists' => str_contains($routes, "'/resident/vehicles'") && str_contains($controller, 'public function vehicles'),
    'resident document view is ownership-scoped' => str_contains($memberController, 'Auth::isResident()') && str_contains($memberController, "document['member_id']"),
    'resident vehicle self-service is available' => str_contains($controller, 'public function storeVehicle') && str_contains($controller, 'public function updateVehicle') && str_contains($controller, 'public function deleteVehicle') && str_contains($routes, "'/resident/vehicles/{id}'"),
    'resident document self-service is available' => str_contains($controller, 'public function storeDocument') && str_contains($controller, 'public function deleteDocument') && str_contains($routes, "'/resident/documents'"),
    'vehicle page has add and edit controls' => str_contains($vehiclesView, 'Add Vehicle') && str_contains($vehiclesView, 'Edit Vehicle') && str_contains($vehiclesView, '/resident/vehicles/'),
    'document page has upload and remove controls' => str_contains($documentsView, 'Add Document') && str_contains($documentsView, 'Upload Document') && str_contains($documentsView, '/resident/documents/'),
    'resident documents view is read-only' => str_contains($documentsView, 'My Documents') && str_contains($documentsView, '/documents/'),
    'resident vehicles view is read-only' => str_contains($vehiclesView, 'My Vehicles') && str_contains($vehiclesView, 'registration_number'),
    'member detail lease uses modal' => str_contains($memberView, 'id="leaseModal"') && str_contains($memberView, 'agreement_doc'),
    'member controller scopes resident operations to current society' => str_contains($memberController, "\$member['society_id']") && str_contains($memberController, 'Society::currentId()'),
    'member creation validates flat society ownership' => str_contains($memberController, "\$flat['society_id']") && str_contains($memberController, 'Flat::find($flatId)'),
    'vehicle controller scopes CRUD to current society' => str_contains($vehicleController, "\$vehicleMember['society_id']") && str_contains($vehicleController, 'Vehicle::find((int) $id)'),
    'parking operations enforce society ownership' => str_contains($vehicleController, "ParkingSlot::find((int) \$id)") && str_contains($vehicleController, "Flat::find(\$flatId)") && str_contains($vehicleController, "ParkingAllocation::find((int) \$allocationId)") && str_contains($vehicleController, "\$rate['society_id']"),
    'parking allocation model exposes lookup' => str_contains(file_get_contents('app/Models/ParkingAllocation.php'), 'public static function find(int $id)'),
    'flat lookup exposes society for ownership checks' => str_contains(file_get_contents('app/Models/Flat.php'), 'w.society_id'),
    'vehicle management uses modal editing' => str_contains(file_get_contents('app/Views/vehicles/index.php'), 'app-form-modal') && str_contains(file_get_contents('app/Views/vehicles/index.php'), 'Edit Vehicle') && !str_contains(file_get_contents('app/Views/vehicles/index.php'), 'data-bs-toggle="collapse"'),
    'lease owner is constrained to tenant flat and society' => str_contains($memberController, "\$owner['flat_id']") && str_contains($memberController, "\$owner['society_id']") && str_contains($memberController, "\$owner['member_type'] !== 'owner'"),
    'lease agreement is society-scoped' => str_contains($memberController, "\$tenantMember['society_id']") && str_contains($memberController, 'serveLeaseDocument'),
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
