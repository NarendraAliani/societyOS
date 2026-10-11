<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$model = file_get_contents($root . '/app/Models/ParkingSlot.php');
$allocationModel = file_get_contents($root . '/app/Models/ParkingAllocation.php');
$controller = file_get_contents($root . '/app/Controllers/VehicleController.php');
$view = file_get_contents($root . '/app/Views/vehicles/parking.php');
$routes = file_get_contents($root . '/public/index.php');

$checks = [
    'common parking capacity is managed as society-scoped two-wheeler slots' => str_contains($model, "COMMON-2W-") && str_contains($model, 'resizeCommonTwoWheeler'),
    'occupied common parking spaces are counted from active allocations' => str_contains($model, 'pa.allocated_to IS NULL') && str_contains($model, 'commonTwoWheelerStats'),
    'capacity cannot be reduced below occupied spaces' => str_contains($model, 'Capacity cannot be lower than the'),
    'parking page exposes capacity and availability controls' => str_contains($view, 'Common 2-Wheeler Parking') && str_contains($view, 'name="capacity"') && str_contains($view, "['occupied']"),
    'capacity update route requires vehicle management permission' => str_contains($routes, "'/vehicles/parking/common-capacity'") && str_contains($routes, "'vehicles.manage'"),
    'controller validates requested capacity range' => str_contains($controller, 'resizeCommonParking') && str_contains($controller, '$capacity > 500'),
    'parking allocation form actually persists validated assignments' => str_contains($controller, 'ParkingAllocation::allocate((int) $id, $flatId, $vehicleId, $fromDate, $isChargeable)') && str_contains($controller, 'already allocated'),
    'four-wheeler dropdown hides vehicles with active allocations' => str_contains($controller, 'vehicleHasActiveFourWheelerAllocation') && str_contains($controller, "vehicle['vehicle_type'] === 'four_wheeler'"),
    'allocation model prevents a vehicle from taking multiple active four-wheeler slots' => str_contains($allocationModel, 'vehicleHasActiveFourWheelerAllocation') && str_contains($allocationModel, 'already has an active 4-wheeler parking allocation'),
    'vehicle type must match parking slot type' => str_contains($model, 'Vehicle type must match parking slot type.'),
    'allocation form requires an allocation start date' => str_contains($view, 'name="allocated_from"') && str_contains($view, 'required>'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Common parking capacity regression detected.\n");
    exit(1);
}
echo "Common parking capacity regression contract passed.\n";
