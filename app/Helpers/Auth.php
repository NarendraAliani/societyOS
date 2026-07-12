<?php

declare(strict_types=1);

namespace App\Helpers;

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role_name'] ?? null;
    }

    /** @var string[]|null */
    private static ?array $permissions = null;

    public static function setPermissions(array $permissions): void
    {
        $_SESSION['permissions'] = $permissions;
        self::$permissions = $permissions;
    }

    public static function can(string $permissionKey): bool
    {
        $permissions = self::$permissions ?? ($_SESSION['permissions'] ?? []);
        return in_array($permissionKey, $permissions, true);
    }

    public static function login(array $user, array $permissions): void
    {
        Session::regenerate();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['society_id'] = $user['society_id'];
        $_SESSION['role_name'] = $user['role_name'];
        self::setPermissions($permissions);
    }

    public static function refreshName(string $name): void
    {
        $_SESSION['user_name'] = $name;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
