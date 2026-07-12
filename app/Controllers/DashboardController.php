<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Asset;
use App\Models\AssetAmc;
use App\Models\Society;

final class DashboardController
{
    private const EXPIRY_WINDOW_DAYS = 30;

    public function index(): void
    {
        $society = Society::current();
        $societyId = (int) $_SESSION['society_id'];
        $stats = Society::dashboardStats($societyId);
        $userName = $_SESSION['user_name'] ?? '';
        $roleName = Auth::role();

        $expiringAmc = AssetAmc::expiringWithin($societyId, self::EXPIRY_WINDOW_DAYS);
        $expiringWarranties = Asset::warrantiesExpiringWithin($societyId, self::EXPIRY_WINDOW_DAYS);

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
