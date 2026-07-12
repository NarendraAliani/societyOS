<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\Society;
use App\Models\User;

final class AuthController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_WINDOW_SECONDS = 900;

    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $error = 'Session expired. Please try again.';
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $societyId = Society::currentId();

        if ($this->isRateLimited($email)) {
            $error = 'Too many failed attempts. Try again later.';
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $user = User::findByEmail($societyId, $email);

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            User::logLoginHistory($user['id'] ?? null, $email, 'failed');
            $error = 'Invalid credentials.';
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $permissions = User::permissionsForRole((int) $user['role_id']);
        Auth::login($user, $permissions);
        User::recordLogin((int) $user['id']);
        User::logLoginHistory((int) $user['id'], $email, 'success');

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    private function isRateLimited(string $email): bool
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM login_history
             WHERE email_attempted = :email AND status = "failed"
             AND created_at > (NOW() - INTERVAL :window SECOND)'
        );
        $stmt->execute(['email' => $email, 'window' => self::LOCKOUT_WINDOW_SECONDS]);
        return (int) $stmt->fetchColumn() >= self::MAX_LOGIN_ATTEMPTS;
    }
}
