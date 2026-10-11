<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controller = file_get_contents($root . '/app/Controllers/VehicleController.php');
$view = file_get_contents($root . '/app/Views/vehicles/parking_allotment.php');
$parkingView = file_get_contents($root . '/app/Views/vehicles/parking.php');
$vehiclesView = file_get_contents($root . '/app/Views/vehicles/index.php');
$routes = file_get_contents($root . '/public/index.php');
$allocationModel = file_get_contents($root . '/app/Models/ParkingAllocation.php');

$checks = [
    'dedicated resident-first parking allotment page and routes exist' => str_contains($controller, 'parkingAllotment') && str_contains($routes, "'/vehicles/parking-allotment'"),
    'parking allotment is restricted to authorised vehicle managers' => str_contains($routes, "'/vehicles/parking-allotment'") && str_contains($routes, "'vehicles.manage'"),
    'interface selects flat, resident vehicle, compatible available slot and allocation date' => str_contains($view, 'name="flat_id"') && str_contains($view, 'name="vehicle_id"') && str_contains($view, 'name="parking_slot_id"') && str_contains($view, 'name="allocated_from"'),
    'flat selection filters vehicle options and slot type filters compatible slots' => str_contains($view, 'data-flat-id') && str_contains($view, 'data-slot-type'),
    'all currently allocated vehicles are excluded from choices without restricting a flat to one vehicle' => str_contains($controller, 'assignedVehicleIds') && str_contains($controller, 'if (isset($assignedVehicleIds[(int) $vehicle[\'id\']]))') && str_contains($view, 'data-flat-id'),
    'flat dropdown is limited to flats with an eligible registered vehicle and compatible free slot' => str_contains($controller, '$eligibleFlatIds') && str_contains($controller, 'Flat::allForSociety($societyId)'),
    'form submits through existing validated allocation model' => str_contains($controller, 'ParkingAllocation::allocate($slotId, $flatId, $vehicleId, $fromDate, $billing === \'paid\')'),
    'parking overview links directly to resident-first allotment' => str_contains($parkingView, '/vehicles/parking-allotment') && str_contains($parkingView, 'Allot Vehicle to Parking'),
    'vehicle listing prominently links to vehicle parking allotment' => str_contains($vehiclesView, '/vehicles/parking-allotment') && str_contains($vehiclesView, 'Allot Parking to Vehicle'),
    'model prevents any vehicle from having multiple active parking assignments and uses the current guard method' => str_contains($allocationModel, 'vehicleHasActiveAllocation($vehicleId)') && !str_contains($allocationModel, 'vehicleHasActiveFourWheelerAllocation') && str_contains($controller, 'foreach ($vehicles as $vehicle)') && str_contains($controller, '$eligibleFlatIds[(int) $vehicle[\'flat_id\']] = true'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Resident-first parking allotment regression detected.\n");
    exit(1);
}
echo "Resident-first parking allotment regression contract passed.\n";
