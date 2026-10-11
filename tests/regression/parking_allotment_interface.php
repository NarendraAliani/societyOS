<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controller = file_get_contents($root . '/app/Controllers/VehicleController.php');
$view = file_get_contents($root . '/app/Views/vehicles/parking_allotment.php');
$parkingView = file_get_contents($root . '/app/Views/vehicles/parking.php');
$routes = file_get_contents($root . '/public/index.php');
$allocationModel = file_get_contents($root . '/app/Models/ParkingAllocation.php');

$checks = [
    'dedicated resident-first parking allotment page and routes exist' => str_contains($controller, 'parkingAllotment') && str_contains($routes, "'/vehicles/parking-allotment'"),
    'parking allotment is restricted to authorised vehicle managers' => str_contains($routes, "'/vehicles/parking-allotment', [VehicleController::class, 'parkingAllotment'], [$auth, $backOffice, $can('vehicles.manage')]"),
    'interface selects flat, resident vehicle, compatible available slot and allocation date' => str_contains($view, 'name="flat_id"') && str_contains($view, 'name="vehicle_id"') && str_contains($view, 'name="parking_slot_id"') && str_contains($view, 'name="allocated_from"'),
    'flat selection filters vehicle options and slot type filters compatible slots' => str_contains($view, 'data-flat-id') && str_contains($view, 'data-slot-type'),
    'currently allocated vehicles are excluded from four-wheeler choices' => str_contains($controller, 'assignedVehicleIds') && str_contains($controller, "vehicle['vehicle_type'] !== 'four_wheeler'"),
    'form submits through existing validated allocation model' => str_contains($controller, 'ParkingAllocation::allocate($slotId, $flatId, $vehicleId, $fromDate, $billing === \'paid\')'),
    'parking overview links directly to resident-first allotment' => str_contains($parkingView, '/vehicles/parking-allotment') && str_contains($parkingView, 'Allot Vehicle to Parking'),
    'model prevents duplicate active four-wheeler assignments' => str_contains($allocationModel, 'vehicleHasActiveFourWheelerAllocation'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Resident-first parking allotment regression detected.\\n");
    exit(1);
}
echo "Resident-first parking allotment regression contract passed.\\n";
