<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Society;

final class DashboardController
{
    public function index(): void
    {
        $society = Society::current();
        $stats = Society::dashboardStats((int) $_SESSION['society_id']);
        $userName = $_SESSION['user_name'] ?? '';
        $roleName = Auth::role();

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
