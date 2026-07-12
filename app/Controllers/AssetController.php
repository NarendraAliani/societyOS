<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetAmc;
use App\Models\AssetCategory;
use App\Models\AssetService;
use App\Models\Society;
use App\Models\Vendor;

final class AssetController
{
    public function index(): void
    {
        $pageTitle = 'Assets';
        $assets = Asset::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/assets/index.php';
    }

    public function create(): void
    {
        $pageTitle = 'Add Asset';
        $categories = AssetCategory::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/assets/create.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($categoryId <= 0 || $name === '') {
            Flash::set('error', 'Category and name are required.');
            header('Location: /assets/create');
            exit;
        }

        $cost = $_POST['purchase_cost'] ?? '';
        $id = Asset::create(Society::currentId(), [
            'category_id' => $categoryId,
            'name' => $name,
            'purchase_date' => $_POST['purchase_date'] ?? '',
            'purchase_cost' => is_numeric($cost) ? $cost : '',
            'warranty_expiry' => $_POST['warranty_expiry'] ?? '',
            'location' => trim((string) ($_POST['location'] ?? '')),
        ]);

        ActivityLog::log('assets', 'create', "Added asset \"{$name}\"");
        Flash::set('success', "Asset \"{$name}\" added.");
        header("Location: /assets/{$id}");
        exit;
    }

    public function show(string $id): void
    {
        $pageTitle = 'Asset Detail';
        $asset = Asset::find((int) $id);
        if (!$asset) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $amcRecords = AssetAmc::forAsset((int) $id);
        $serviceRecords = AssetService::forAsset((int) $id);
        $vendors = Vendor::allForSociety(Society::currentId());
        $categories = AssetCategory::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/assets/show.php';
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($categoryId <= 0 || $name === '') {
            Flash::set('error', 'Category and name are required.');
            header("Location: /assets/{$id}");
            exit;
        }

        $cost = $_POST['purchase_cost'] ?? '';
        Asset::update((int) $id, [
            'category_id' => $categoryId,
            'name' => $name,
            'purchase_date' => $_POST['purchase_date'] ?? '',
            'purchase_cost' => is_numeric($cost) ? $cost : '',
            'warranty_expiry' => $_POST['warranty_expiry'] ?? '',
            'location' => trim((string) ($_POST['location'] ?? '')),
        ]);

        ActivityLog::log('assets', 'update', "Updated asset \"{$name}\" (id {$id})");
        Flash::set('success', 'Asset updated.');
        header("Location: /assets/{$id}");
        exit;
    }

    public function setStatus(string $id): void
    {
        $this->verifyCsrf();
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['active', 'under_repair', 'disposed'], true)) {
            Asset::setStatus((int) $id, $status);
            Flash::set('success', 'Asset status updated.');
        }
        header("Location: /assets/{$id}");
        exit;
    }

    public function storeAmc(string $id): void
    {
        $this->verifyCsrf();

        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        if (!$startDate || !$endDate) {
            Flash::set('error', 'Start and end dates are required.');
            header("Location: /assets/{$id}");
            exit;
        }

        $cost = $_POST['cost'] ?? '';
        AssetAmc::create(
            (int) $id,
            (int) ($_POST['vendor_id'] ?? 0) ?: null,
            $startDate,
            $endDate,
            is_numeric($cost) ? (float) $cost : null
        );

        ActivityLog::log('assets', 'amc', "Added AMC record for asset id {$id}");
        Flash::set('success', 'AMC record added.');
        header("Location: /assets/{$id}");
        exit;
    }

    public function storeService(string $id): void
    {
        $this->verifyCsrf();

        $serviceDate = $_POST['service_date'] ?? '';
        if (!$serviceDate) {
            Flash::set('error', 'Service date is required.');
            header("Location: /assets/{$id}");
            exit;
        }

        $cost = $_POST['cost'] ?? '';
        AssetService::create(
            (int) $id,
            $serviceDate,
            trim((string) ($_POST['description'] ?? '')) ?: null,
            is_numeric($cost) ? (float) $cost : null,
            $_POST['next_due_date'] ?? ''
        );

        Flash::set('success', 'Service record added.');
        header("Location: /assets/{$id}");
        exit;
    }

    public function categories(): void
    {
        $pageTitle = 'Asset Categories';
        $categories = AssetCategory::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/assets/categories.php';
    }

    public function storeCategory(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
        } else {
            AssetCategory::create(Society::currentId(), $name);
            Flash::set('success', "Category \"{$name}\" added.");
        }
        header('Location: /assets/categories');
        exit;
    }

    public function updateCategory(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
        } else {
            AssetCategory::update((int) $id, $name);
            Flash::set('success', 'Category updated.');
        }
        header('Location: /assets/categories');
        exit;
    }

    public function deleteCategory(string $id): void
    {
        $this->verifyCsrf();

        try {
            AssetCategory::delete((int) $id);
            Flash::set('success', 'Category deleted.');
        } catch (\PDOException $e) {
            Flash::set('error', 'This category has assets assigned to it and cannot be deleted. Reassign or remove those assets first.');
        }

        header('Location: /assets/categories');
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
