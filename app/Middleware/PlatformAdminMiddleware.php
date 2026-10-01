<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\PlatformAuth;

final class PlatformAdminMiddleware
{
    public static function handle(): void
    {
        if (!PlatformAuth::check()) { header('Location: /platform/login'); exit; }
        if (PlatformAuth::mustChangePassword()) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            if ($path !== '/platform/password') { header('Location: /platform/password'); exit; }
        }
    }
}
