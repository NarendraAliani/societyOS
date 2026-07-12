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
    }
}
