<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        if (Auth::mustChangePassword()) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

            if ($path !== '/profile/password') {
                header('Location: /profile/password?required=1');
                exit;
            }
        }
    }
}
