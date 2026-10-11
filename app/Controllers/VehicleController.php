<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Flat;
use App\Models\Member;
use App\Models\ParkingAllocation;
use App\Models\ParkingRate;
use App\Models\ParkingSlot;
use App\Models\Society;
use App\Models\Vehicle;

final class VehicleController
{
    public function index(): void
    {
        $pageTitle = 'Vehicles';
        $vehicles = Vehicle::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/vehicles/index.php';
    }

    public function create(): void
    {
        $pageTitle = 'Add Vehicle';
        $members = Member::allForSociety(Society::currentId());
        $returnToMember = isset($_GET['return_to_member']) && is_numeric($_GET['return_to_member'])
            ? (int) $_GET['return_to_member']
            : null;
        require __DIR__ . '/../Views/vehicles/create.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $memberId = (int) ($_POST['member_id'] ?? 0);
        $vehicleType = ($_POST['vehicle_type'] ?? '') === 'two_wheeler' ? 'two_wheeler' : 'four_wheeler';
        $registration = strtoupper(trim((string) ($_POST['registration_number'] ?? '')));
        $returnToMember = is_numeric($_POST['return_to_member'] ?? '') ? (int) $_POST['return_to_member'] : null;
        $backToCreate = '/vehicles/create' . ($returnToMember ? "?return_to_member={$returnToMember}" : '');

        $member = $memberId > 0 ? Member::find($memberId) : null;
        if (!$member || (int) $member['society_id'] !== Society::currentId() || $registration === '') {
            Flash::set('error', 'A valid resident and registration number are required.');
            header("Location: {$backToCreate}");
            exit;
        }

        if (Vehicle::registrationExists($registration)) {
            Flash::set('error', "Registration number \"{$registration}\" is already on file.");
            header("Location: {$backToCreate}");
            exit;
        }

        Vehicle::create([
            'member_id' => $memberId,
            'vehicle_type' => $vehicleType,
            'registration_number' => $registration,
            'make' => trim((string) ($_POST['make'] ?? '')),
            'model' => trim((string) ($_POST['model'] ?? '')),
            'color' => trim((string) ($_POST['color'] ?? '')),
        ]);

        ActivityLog::log('vehicles', 'create', "Added vehicle \"{$registration}\"");
        Flash::set('success', "Vehicle \"{$registration}\" added.");
        header('Location: ' . ($returnToMember ? "/members/{$returnToMember}" : '/vehicles'));
        exit;
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        $vehicleType = ($_POST['vehicle_type'] ?? '') === 'two_wheeler' ? 'two_wheeler' : 'four_wheeler';
        $registration = strtoupper(trim((string) ($_POST['registration_number'] ?? '')));
        $returnToMember = is_numeric($_POST['return_to_member'] ?? '') ? (int) $_POST['return_to_member'] : null;
        $returnPath = $returnToMember ? "/members/{$returnToMember}" : '/vehicles';

        $vehicle = Vehicle::find((int) $id);
        $vehicleMember = $vehicle ? Member::find((int) $vehicle['member_id']) : null;
        if (!$vehicle || !$vehicleMember || (int) $vehicleMember['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Vehicle not found.');
            header("Location: {$returnPath}");
            exit;
        }

        if ($registration === '') {
            Flash::set('error', 'Registration number is required.');
            header("Location: {$returnPath}");
            exit;
        }

        if (Vehicle::registrationExists($registration, (int) $id)) {
            Flash::set('error', "Registration number \"{$registration}\" is already on file.");
            header("Location: {$returnPath}");
            exit;
        }

        Vehicle::update((int) $id, [
            'vehicle_type' => $vehicleType,
            'registration_number' => $registration,
            'make' => trim((string) ($_POST['make'] ?? '')),
            'model' => trim((string) ($_POST['model'] ?? '')),
            'color' => trim((string) ($_POST['color'] ?? '')),
        ]);

        Flash::set('success', 'Vehicle updated.');
        header("Location: {$returnPath}");
        exit;
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();

        $returnToMember = is_numeric($_POST['return_to_member'] ?? '') ? (int) $_POST['return_to_member'] : null;

        $vehicle = Vehicle::find((int) $id);
        $vehicleMember = $vehicle ? Member::find((int) $vehicle['member_id']) : null;
        if (!$vehicle || !$vehicleMember || (int) $vehicleMember['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Vehicle not found.');
            header('Location: /vehicles');
            exit;
        }
        Vehicle::delete((int) $id);
        ActivityLog::log('vehicles', 'delete', 'Removed vehicle "' . ($vehicle['registration_number'] ?? $id) . '"');
        Flash::set('success', 'Vehicle removed.');
        header('Location: ' . ($returnToMember ? "/members/{$returnToMember}" : '/vehicles'));
        exit;
    }

    public function parkingAllotment(): void
    {
        $pageTitle = 'Parking Allotment';
        $societyId = Society::currentId();
        $vehicles = Vehicle::allForSociety($societyId);
        $slots = array_values(array_filter(
            ParkingSlot::allForSociety($societyId),
            static fn (array $slot): bool => empty($slot['is_allocated'])
        ));

        $stmt = db()->prepare(
            'SELECT pa.id AS allocation_id, pa.parking_slot_id, pa.vehicle_id, pa.flat_id,
                    pa.allocated_from, pa.allocated_to, pa.is_chargeable,
                    ps.slot_number, ps.slot_type, v.registration_number, v.make, v.model,
                    m.name AS resident_name, f.flat_number, w.name AS wing_name
             FROM parking_allocations pa
             JOIN parking_slots ps ON ps.id = pa.parking_slot_id
             JOIN flats f ON f.id = pa.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             LEFT JOIN vehicles v ON v.id = pa.vehicle_id
             LEFT JOIN members m ON m.id = v.member_id
             WHERE ps.society_id = :sid AND pa.allocated_to IS NULL
             ORDER BY w.name, f.flat_number, ps.slot_number'
        );
        $stmt->execute(['sid' => $societyId]);
        $activeAllocations = $stmt->fetchAll();

        $assignedVehicleIds = [];
        foreach ($activeAllocations as $allocation) {
            if ($allocation['vehicle_id'] !== null && $allocation['slot_type'] === 'four_wheeler') {
                $assignedVehicleIds[(int) $allocation['vehicle_id']] = true;
            }
        }
        $vehicles = array_values(array_filter($vehicles, static function (array $vehicle) use ($assignedVehicleIds, $slots): bool {
            if ($vehicle['vehicle_type'] === 'four_wheeler' && isset($assignedVehicleIds[(int) $vehicle['id']])) {
                return false;
            }
            foreach ($slots as $slot) {
                if ($slot['slot_type'] === $vehicle['vehicle_type']) {
                    return true;
                }
            }
            return false;
        }));

        // Only show flats with a registered resident vehicle that can be assigned to a free compatible slot.
        $eligibleFlatIds = [];
        foreach ($vehicles as $vehicle) {
            $eligibleFlatIds[(int) $vehicle['flat_id']] = true;
        }
        $flats = array_values(array_filter(
            Flat::allForSociety($societyId),
            static fn (array $flat): bool => isset($eligibleFlatIds[(int) $flat['id']])
        ));

        require __DIR__ . '/../Views/vehicles/parking_allotment.php';
    }

    public function storeParkingAllotment(): void
    {
        $this->verifyCsrf();
        $flatId = (int) ($_POST['flat_id'] ?? 0);
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
        $slotId = (int) ($_POST['parking_slot_id'] ?? 0);
        $fromDate = trim((string) ($_POST['allocated_from'] ?? date('Y-m-d')));
        $billing = (string) ($_POST['billing_status'] ?? 'paid');
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $fromDate);
        $validDate = $parsedDate && $parsedDate->format('Y-m-d') === $fromDate;
        $flat = $flatId > 0 ? Flat::find($flatId) : null;
        $vehicle = $vehicleId > 0 ? Vehicle::find($vehicleId) : null;
        $slot = $slotId > 0 ? ParkingSlot::find($slotId) : null;
        $vehicleMember = $vehicle ? Member::find((int) $vehicle['member_id']) : null;

        if (!$flat || (int) ($flat['society_id'] ?? 0) !== Society::currentId()
            || !$vehicle || !$vehicleMember
            || (int) $vehicleMember['flat_id'] !== $flatId
            || (int) $vehicleMember['society_id'] !== Society::currentId()
            || !$slot || (int) $slot['society_id'] !== Society::currentId()
            || $vehicle['vehicle_type'] !== $slot['slot_type']
            || !$validDate || !in_array($billing, ['paid', 'free'], true)) {
            Flash::set('error', 'Please select a valid resident flat, matching registered vehicle, available compatible parking slot, allocation date and billing option.');
            header('Location: /vehicles/parking-allotment');
            exit;
        }

        if (ParkingAllocation::activeForSlot($slotId)) {
            Flash::set('error', 'That parking slot has just been allocated. Please choose another available slot.');
            header('Location: /vehicles/parking-allotment');
            exit;
        }

        try {
            ParkingAllocation::allocate($slotId, $flatId, $vehicleId, $fromDate, $billing === 'paid');
            ActivityLog::log('vehicles', 'parking_allocate', 'Allocated vehicle ' . $vehicle['registration_number'] . ' to parking slot ' . $slot['slot_number']);
            Flash::set('success', 'Vehicle allocated to parking successfully.');
        } catch (\Throwable $e) {
            Flash::set('error', $e->getMessage() === 'This vehicle already has an active 4-wheeler parking allocation.'
                ? $e->getMessage()
                : 'Unable to allocate the vehicle. Please verify the selections and try again.');
        }

        header('Location: /vehicles/parking-allotment');
        exit;
    }

    public function parkingIndex(): void
    {
        $pageTitle = 'Parking';
        $slots = ParkingSlot::allForSociety(Society::currentId());
        $commonParking = ParkingSlot::commonTwoWheelerStats(Society::currentId());
        require __DIR__ . '/../Views/vehicles/parking.php';
    }

    public function resizeCommonParking(): void
    {
        $this->verifyCsrf();
        $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT);
        if ($capacity === false || $capacity < 0 || $capacity > 500) {
            Flash::set('error', 'Enter a whole-number capacity between 0 and 500.');
            header('Location: /vehicles/parking');
            exit;
        }

        try {
            $stats = ParkingSlot::resizeCommonTwoWheeler(Society::currentId(), $capacity);
            Flash::set('success', "Common two-wheeler parking updated: {$stats['capacity']} spaces, {$stats['occupied']} occupied.");
        } catch (\DomainException $e) {
            Flash::set('error', $e->getMessage());
        } catch (\Throwable $e) {
            Flash::set('error', 'Unable to update common parking capacity. Please try again.');
        }

        header('Location: /vehicles/parking');
        exit;
    }

    public function storeSlot(): void
    {
        $this->verifyCsrf();

        $slotNumber = trim((string) ($_POST['slot_number'] ?? ''));
        $slotType = ($_POST['slot_type'] ?? '') === 'two_wheeler' ? 'two_wheeler' : 'four_wheeler';

        if ($slotNumber === '') {
            Flash::set('error', 'Slot number is required.');
        } else {
            ParkingSlot::create(Society::currentId(), $slotNumber, $slotType);
            Flash::set('success', "Parking slot \"{$slotNumber}\" created.");
        }
        header('Location: /vehicles/parking');
        exit;
    }

    public function updateSlot(string $id): void
    {
        $this->verifyCsrf();

        $slotNumber = trim((string) ($_POST['slot_number'] ?? ''));
        $slotType = ($_POST['slot_type'] ?? '') === 'two_wheeler' ? 'two_wheeler' : 'four_wheeler';

        $slot = ParkingSlot::find((int) $id);
        if (!$slot || (int) $slot['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Parking slot not found.');
        } elseif ($slotNumber === '') {
            Flash::set('error', 'Slot number is required.');
        } else {
            ParkingSlot::update((int) $id, $slotNumber, $slotType);
            Flash::set('success', 'Parking slot updated.');
        }
        header('Location: /vehicles/parking');
        exit;
    }

    public function deleteSlot(string $id): void
    {
        $this->verifyCsrf();

        $slot = ParkingSlot::find((int) $id);
        if (!$slot || (int) $slot['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Parking slot not found.');
            header('Location: /vehicles/parking');
            exit;
        }

        try {
            ParkingSlot::delete((int) $id);
            Flash::set('success', 'Parking slot deleted.');
        } catch (\PDOException $e) {
            Flash::set('error', 'This slot has allocation or billing history and cannot be deleted.');
        }

        header('Location: /vehicles/parking');
        exit;
    }

    public function parkingRates(): void
    {
        $pageTitle = 'Parking Rates';
        $rates = ParkingRate::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/vehicles/parking_rates.php';
    }

    public function storeParkingRate(): void
    {
        $this->verifyCsrf();

        $slotType = ($_POST['slot_type'] ?? '') === 'two_wheeler' ? 'two_wheeler' : 'four_wheeler';
        $amount = $_POST['amount'] ?? '';
        $effectiveFrom = $_POST['effective_from'] ?? '';

        if (!is_numeric($amount) || (float) $amount <= 0 || !$effectiveFrom) {
            Flash::set('error', 'A positive amount and an effective date are required.');
            header('Location: /vehicles/parking/rates');
            exit;
        }

        try {
            ParkingRate::create(Society::currentId(), $slotType, (float) $amount, $effectiveFrom, Auth::id());
            Flash::set('success', "Rate change scheduled from {$effectiveFrom}.");
        } catch (\PDOException $e) {
            Flash::set('error', 'A rate is already scheduled for that type and date. Pick a different date, or remove the existing scheduled entry first.');
        }

        header('Location: /vehicles/parking/rates');
        exit;
    }

    public function deleteParkingRate(string $id): void
    {
        $this->verifyCsrf();

        $rate = ParkingRate::find((int) $id);
        if (!$rate || (int) $rate['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Parking rate not found.');
            header('Location: /vehicles/parking/rates');
            exit;
        }

        if (ParkingRate::deleteIfFuture((int) $id)) {
            Flash::set('success', 'Scheduled rate change removed.');
        } else {
            Flash::set('error', 'Only a not-yet-effective (future-dated) rate can be removed. Past and current rates are kept as history.');
        }

        header('Location: /vehicles/parking/rates');
        exit;
    }

    public function slotDetail(string $id): void
    {
        $pageTitle = 'Parking Slot Detail';
        $slot = ParkingSlot::find((int) $id);
        if (!$slot || (int) $slot['society_id'] !== Society::currentId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $history = ParkingAllocation::historyForSlot((int) $id);
        $vehicles = Vehicle::allForSociety(Society::currentId());
        $slotType = $slot['slot_type'];
        $vehicles = array_values(array_filter($vehicles, static function (array $vehicle) use ($slotType): bool {
            if ($vehicle['vehicle_type'] !== $slotType) {
                return false;
            }
            return $slotType !== 'four_wheeler'
                || !ParkingAllocation::vehicleHasActiveFourWheelerAllocation((int) $vehicle['id']);
        }));
        // A flat is eligible only when it has a registered vehicle that can be assigned to this slot.
        $eligibleFlatIds = [];
        foreach ($vehicles as $vehicle) {
            $eligibleFlatIds[(int) $vehicle['flat_id']] = true;
        }
        $flats = array_values(array_filter(
            Flat::allForSociety(Society::currentId()),
            static fn (array $flat): bool => isset($eligibleFlatIds[(int) $flat['id']])
        ));
        require __DIR__ . '/../Views/vehicles/slot_detail.php';
    }

    public function allocate(string $id): void
    {
        $this->verifyCsrf();

        $flatId = (int) ($_POST['flat_id'] ?? 0);
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0) ?: null;
        $fromDate = trim((string) ($_POST['allocated_from'] ?? date('Y-m-d')));
        $isChargeable = ($_POST['billing_status'] ?? 'paid') !== 'free';
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $fromDate);
        $validDate = $parsedDate && $parsedDate->format('Y-m-d') === $fromDate;

        $slot = ParkingSlot::find((int) $id);
        $flat = $flatId > 0 ? Flat::find($flatId) : null;
        $vehicle = $vehicleId ? Vehicle::find($vehicleId) : null;
        $vehicleMember = $vehicle ? Member::find((int) $vehicle['member_id']) : null;
        $validVehicle = !$vehicleId || ($vehicle && $vehicleMember
            && (int) $vehicleMember['society_id'] === Society::currentId()
            && (int) $vehicleMember['flat_id'] === $flatId
            && $slot && $vehicle['vehicle_type'] === $slot['slot_type']);
        $activeAllocation = $slot ? ParkingAllocation::activeForSlot((int) $id) : null;
        $vehicleAlreadyAllocated = $vehicleId && $slot && $slot['slot_type'] === 'four_wheeler'
            && ParkingAllocation::vehicleHasActiveFourWheelerAllocation($vehicleId);

        if (!$slot || (int) $slot['society_id'] !== Society::currentId() || !$flat || (int) $flat['society_id'] !== Society::currentId() || !$validVehicle || !$validDate) {
            Flash::set('error', 'A valid society slot, flat, matching vehicle type, and allocation date are required.');
        } elseif ($activeAllocation) {
            Flash::set('error', 'This parking slot is already allocated.');
        } elseif ($vehicleAlreadyAllocated) {
            Flash::set('error', 'This vehicle already has an active 4-wheeler parking allocation.');
        } else {
            try {
                ParkingAllocation::allocate((int) $id, $flatId, $vehicleId, $fromDate, $isChargeable);
                Flash::set('success', 'Slot allocated.');
            } catch (\Throwable $e) {
                error_log(sprintf(
                    'Parking allocation failed (slot=%d, flat=%d, vehicle=%s): %s',
                    (int) $id,
                    $flatId,
                    $vehicleId === null ? 'unassigned' : (string) $vehicleId,
                    $e->getMessage()
                ));
                $message = $e instanceof \DomainException || $e instanceof \InvalidArgumentException
                    ? $e->getMessage()
                    : 'Unable to allocate this parking slot due to a system error. Please contact the administrator.';
                Flash::set('error', $message);
            }
        }
        header("Location: /vehicles/parking/{$id}");
        exit;
    }

    public function release(string $allocationId): void
    {
        $this->verifyCsrf();
        $slotId = (int) ($_POST['slot_id'] ?? 0);
        $allocation = ParkingAllocation::find((int) $allocationId);
        $slot = $allocation ? ParkingSlot::find((int) $allocation['parking_slot_id']) : null;
        if (!$allocation || !$slot || (int) $slot['society_id'] !== Society::currentId() || (int) $allocation['parking_slot_id'] !== $slotId) {
            Flash::set('error', 'Parking allocation not found.');
            header('Location: /vehicles/parking');
            exit;
        }
        ParkingAllocation::release((int) $allocationId);
        Flash::set('success', 'Slot released.');
        header("Location: /vehicles/parking/{$slotId}");
        exit;
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
