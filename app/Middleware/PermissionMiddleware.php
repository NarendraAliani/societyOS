<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;

final class PermissionMiddleware
{
    public static function require(string $permissionKey): callable
    {
        return function () use ($permissionKey): void {
            if (!Auth::can($permissionKey)) {
                http_response_code(403);
                require __DIR__ . '/../Views/errors/403.php';
                exit;
            }
        };
    }
}
