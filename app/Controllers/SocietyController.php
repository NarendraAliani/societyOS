<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Flat;
use App\Models\Floor;
use App\Models\MaintenanceHead;
use App\Models\MaintenanceHeadRate;
use App\Models\Society;
use App\Models\Wing;

final class SocietyController
{
    public function profile(): void
    {
        $pageTitle = 'Society Profile';
        $society = Society::current();
        require __DIR__ . '/../Views/society/profile.php';
    }

    public function updateProfile(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Society name is required.');
            header('Location: /society');
            exit;
        }

        Society::update(Society::currentId(), $_POST);
        ActivityLog::log('society', 'update', 'Updated society profile');
        Flash::set('success', 'Society profile updated.');
        header('Location: /society');
        exit;
    }

    public function wings(): void
    {
        $pageTitle = 'Wings & Flats';
        $wings = Wing::allWithCounts(Society::currentId());
        require __DIR__ . '/../Views/society/wings.php';
    }

    public function storeWing(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Wing name is required.');
        } else {
            Wing::create(Society::currentId(), $name);
            Flash::set('success', "Wing \"{$name}\" created.");
        }
        header('Location: /society/wings');
        exit;
    }

    public function updateWing(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Wing name is required.');
        } else {
            Wing::update((int) $id, $name);
            Flash::set('success', 'Wing updated.');
        }
        header('Location: /society/wings');
        exit;
    }

    public function deleteWing(string $id): void
    {
        $this->verifyCsrf();
        Wing::delete((int) $id);
        Flash::set('success', 'Wing deleted.');
        header('Location: /society/wings');
        exit;
    }

    public function wingDetail(string $id): void
    {
        $pageTitle = 'Wing Detail';
        $wing = Wing::find((int) $id);
        if (!$wing) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $floors = Floor::forWingWithCounts((int) $id);
        require __DIR__ . '/../Views/society/wing_detail.php';
    }

    public function storeFloor(): void
    {
        $this->verifyCsrf();

        $wingId = (int) ($_POST['wing_id'] ?? 0);
        $floorNumber = $_POST['floor_number'] ?? '';

        if ($wingId <= 0 || $floorNumber === '' || !is_numeric($floorNumber)) {
            Flash::set('error', 'A valid floor number is required.');
        } else {
            Floor::create($wingId, (int) $floorNumber);
            Flash::set('success', 'Floor added.');
        }
        header("Location: /society/wings/{$wingId}");
        exit;
    }

    public function updateFloor(string $id): void
    {
        $this->verifyCsrf();

        $floor = Floor::find((int) $id);
        $floorNumber = $_POST['floor_number'] ?? '';

        if (!$floor || $floorNumber === '' || !is_numeric($floorNumber)) {
            Flash::set('error', 'A valid floor number is required.');
            header('Location: /society/wings/' . ($floor['wing_id'] ?? ''));
            exit;
        }

        Floor::update((int) $id, (int) $floorNumber);
        Flash::set('success', 'Floor updated.');
        header("Location: /society/wings/{$floor['wing_id']}");
        exit;
    }

    public function deleteFloor(string $id): void
    {
        $this->verifyCsrf();
        $floor = Floor::find((int) $id);
        Floor::delete((int) $id);
        Flash::set('success', 'Floor deleted.');
        header('Location: /society/wings/' . ($floor['wing_id'] ?? ''));
        exit;
    }

    public function floorDetail(string $id): void
    {
        $pageTitle = 'Floor Detail';
        $floor = Floor::find((int) $id);
        if (!$floor) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $flats = Flat::forFloor((int) $id);
        require __DIR__ . '/../Views/society/floor_detail.php';
    }

    public function storeFlat(): void
    {
        $this->verifyCsrf();

        $floorId = (int) ($_POST['floor_id'] ?? 0);
        $flatNumber = trim((string) ($_POST['flat_number'] ?? ''));
        $flatType = trim((string) ($_POST['flat_type'] ?? '')) ?: null;
        $carpetArea = $_POST['carpet_area_sqft'] ?? '';
        $carpetArea = is_numeric($carpetArea) ? (float) $carpetArea : null;

        if ($floorId <= 0 || $flatNumber === '') {
            Flash::set('error', 'Flat number is required.');
        } else {
            Flat::create($floorId, $flatNumber, $flatType, $carpetArea);
            Flash::set('success', "Flat \"{$flatNumber}\" created.");
        }
        header("Location: /society/floors/{$floorId}");
        exit;
    }

    public function updateFlat(string $id): void
    {
        $this->verifyCsrf();

        $flat = Flat::find((int) $id);
        $flatNumber = trim((string) ($_POST['flat_number'] ?? ''));
        $flatType = trim((string) ($_POST['flat_type'] ?? '')) ?: null;
        $carpetArea = $_POST['carpet_area_sqft'] ?? '';
        $carpetArea = is_numeric($carpetArea) ? (float) $carpetArea : null;

        if (!$flat || $flatNumber === '') {
            Flash::set('error', 'Flat number is required.');
            header('Location: /society/floors/' . ($flat['floor_id'] ?? ''));
            exit;
        }

        Flat::update((int) $id, $flatNumber, $flatType, $carpetArea);

        $occupancyStatus = $_POST['occupancy_status'] ?? '';
        if (in_array($occupancyStatus, ['owner_occupied', 'tenant_occupied', 'vacant'], true)) {
            Flat::updateOccupancyStatus((int) $id, $occupancyStatus);
        }

        Flash::set('success', 'Flat updated.');
        header("Location: /society/floors/{$flat['floor_id']}");
        exit;
    }

    public function deleteFlat(string $id): void
    {
        $this->verifyCsrf();
        $flat = Flat::find((int) $id);
        Flat::delete((int) $id);
        Flash::set('success', 'Flat deleted.');
        header('Location: /society/floors/' . ($flat['floor_id'] ?? ''));
        exit;
    }

    public function maintenanceHeads(): void
    {
        $pageTitle = 'Maintenance Configuration';
        $heads = MaintenanceHead::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/society/maintenance_heads.php';
    }

    public function storeMaintenanceHead(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $calculationType = ($_POST['calculation_type'] ?? '') === 'per_sqft' ? 'per_sqft' : 'fixed';
        $amount = $_POST['amount'] ?? '';

        if ($name === '' || !is_numeric($amount)) {
            Flash::set('error', 'Head name and a numeric amount are required.');
        } else {
            MaintenanceHead::create(Society::currentId(), $name, $calculationType, (float) $amount, Auth::id());
            ActivityLog::log('society', 'create', "Created maintenance head \"{$name}\"");
            Flash::set('success', "Maintenance head \"{$name}\" created.");
        }
        header('Location: /society/maintenance-heads');
        exit;
    }

    public function updateMaintenanceHead(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $calculationType = ($_POST['calculation_type'] ?? '') === 'per_sqft' ? 'per_sqft' : 'fixed';

        if ($name === '') {
            Flash::set('error', 'Head name is required.');
        } else {
            MaintenanceHead::update((int) $id, $name, $calculationType);
            ActivityLog::log('society', 'update', "Updated maintenance head \"{$name}\" (id {$id})");
            Flash::set('success', 'Maintenance head updated. To change the amount, use "Manage Rates" below.');
        }
        header('Location: /society/maintenance-heads');
        exit;
    }

    public function maintenanceHeadDetail(string $id): void
    {
        $pageTitle = 'Maintenance Head Rates';
        $head = MaintenanceHead::find((int) $id);
        if (!$head) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $rates = MaintenanceHeadRate::forHead((int) $id);
        require __DIR__ . '/../Views/society/maintenance_head_detail.php';
    }

    public function storeMaintenanceHeadRate(string $id): void
    {
        $this->verifyCsrf();

        $amount = $_POST['amount'] ?? '';
        $effectiveFrom = $_POST['effective_from'] ?? '';

        if (!is_numeric($amount) || (float) $amount <= 0 || !$effectiveFrom) {
            Flash::set('error', 'A positive amount and an effective date are required.');
            header("Location: /society/maintenance-heads/{$id}");
            exit;
        }

        try {
            MaintenanceHeadRate::create((int) $id, (float) $amount, $effectiveFrom, Auth::id());
            ActivityLog::log('society', 'rate_change', "Scheduled rate {$amount} for maintenance head id {$id} effective {$effectiveFrom}");
            Flash::set('success', "Rate change scheduled from {$effectiveFrom}.");
        } catch (\PDOException $e) {
            Flash::set('error', 'A rate is already scheduled for that date. Pick a different date, or remove the existing scheduled entry first.');
        }

        header("Location: /society/maintenance-heads/{$id}");
        exit;
    }

    public function deleteMaintenanceHeadRate(string $rateId): void
    {
        $this->verifyCsrf();

        $rate = MaintenanceHeadRate::find((int) $rateId);
        $headId = $rate['maintenance_head_id'] ?? null;

        if (MaintenanceHeadRate::deleteIfFuture((int) $rateId)) {
            Flash::set('success', 'Scheduled rate change removed.');
        } else {
            Flash::set('error', 'Only a not-yet-effective (future-dated) rate can be removed. Past and current rates are kept as history.');
        }

        header('Location: /society/maintenance-heads/' . $headId);
        exit;
    }

    public function toggleMaintenanceHead(string $id): void
    {
        $this->verifyCsrf();
        MaintenanceHead::toggleActive((int) $id);
        header('Location: /society/maintenance-heads');
        exit;
    }

    public function deleteMaintenanceHead(string $id): void
    {
        $this->verifyCsrf();

        try {
            MaintenanceHead::delete((int) $id);
            Flash::set('success', 'Maintenance head deleted.');
        } catch (\PDOException $e) {
            Flash::set('error', 'This head has already been used on generated bills and cannot be deleted. Mark it Inactive instead.');
        }

        header('Location: /society/maintenance-heads');
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
