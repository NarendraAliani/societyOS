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

    public function parkingIndex(): void
    {
        $pageTitle = 'Parking';
        $slots = ParkingSlot::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/vehicles/parking.php';
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
        $flats = Flat::allForSociety(Society::currentId());
        $vehicles = Vehicle::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/vehicles/slot_detail.php';
    }

    public function allocate(string $id): void
    {
        $this->verifyCsrf();

        $flatId = (int) ($_POST['flat_id'] ?? 0);
        $vehicleId = (int) ($_POST['vehicle_id'] ?? 0) ?: null;
        $fromDate = $_POST['allocated_from'] ?? date('Y-m-d');
        $isChargeable = ($_POST['billing_status'] ?? 'paid') !== 'free';

        $slot = ParkingSlot::find((int) $id);
        $flat = $flatId > 0 ? Flat::find($flatId) : null;
        $vehicle = $vehicleId ? Vehicle::find($vehicleId) : null;
        $vehicleMember = $vehicle ? Member::find((int) $vehicle['member_id']) : null;
        $validVehicle = !$vehicleId || ($vehicle && $vehicleMember && (int) $vehicleMember['society_id'] === Society::currentId() && (int) $vehicleMember['flat_id'] === $flatId);
        if (!$slot || (int) $slot['society_id'] !== Society::currentId() || !$flat || (int) $flat['society_id'] !== Society::currentId() || !$validVehicle) {
            Flash::set('error', 'A valid society slot, flat, and matching vehicle are required.');
        } else {
            Flash::set('success', 'Slot allocated.');
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
