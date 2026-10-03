<?php

declare(strict_types=1);

namespace App\Helpers;

final class PlatformAuth
{
    public static function check(): bool
    {
        return !empty($_SESSION['platform_admin_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['platform_admin_id']) ? (int) $_SESSION['platform_admin_id'] : null;
    }

    public static function login(array $admin): void
    {
        Session::regenerate();

        // Platform administration is a separate security context from any society login.
        // Never carry an active society session into the platform console.
        foreach ([
            'user_id', 'user_name', 'society_id', 'role_id', 'role_name',
            'role_assignment_id', 'member_id', 'must_change_password', 'permissions',
        ] as $key) {
            unset($_SESSION[$key]);
        }

        $_SESSION['platform_admin_id'] = (int) $admin['id'];
        $_SESSION['platform_admin_name'] = $admin['name'];
        $_SESSION['platform_must_change_password'] = !empty($admin['must_change_password']);
    }

    public static function mustChangePassword(): bool
    {
        return !empty($_SESSION['platform_must_change_password']);
    }

    public static function clearPasswordChangeRequirement(): void
    {
        unset($_SESSION['platform_must_change_password']);
    }

    public static function logout(): void
    {
        unset(
            $_SESSION['platform_admin_id'],
            $_SESSION['platform_admin_name'],
            $_SESSION['platform_must_change_password']
        );
        Session::regenerate();
    }
}
