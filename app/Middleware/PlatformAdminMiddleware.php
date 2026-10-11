<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\PlatformAuth;
use App\Models\PlatformAdmin;

final class PlatformAdminMiddleware
{
    public static function handle(): void
    {
        $adminId = PlatformAuth::id();
        if ($adminId === null) {
            header('Location: /platform/login');
            exit;
        }

        $admin = PlatformAdmin::findById($adminId);
        if (!$admin || $admin['status'] !== 'active') {
            PlatformAuth::logout();
            header('Location: /platform/login');
            exit;
        }

        // Keep the password-change requirement synchronized with the database so an
        // administrator deactivated/updated elsewhere cannot retain stale session state.
        if (!empty($admin['must_change_password'])) {
            if (!PlatformAuth::mustChangePassword()) {
                $_SESSION['platform_must_change_password'] = true;
            }

            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            if ($path !== '/platform/password') {
                header('Location: /platform/password');
                exit;
            }
        } else {
            PlatformAuth::clearPasswordChangeRequirement();
        }
    }
}
