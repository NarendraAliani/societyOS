<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;

final class BackOfficeMiddleware
{
    public static function handle(): void
    {
        if (Auth::isResident()) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }
}
