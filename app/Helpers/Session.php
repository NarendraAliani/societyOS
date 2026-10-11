<?php

declare(strict_types=1);

namespace App\Helpers;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        require_once dirname(__DIR__, 2) . '/config/app.php';
        $lifetime = (int) config()['session_lifetime'] * 60;

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('societyos_session');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_start();

        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = time();
        } elseif (time() - $_SESSION['_last_activity'] > $lifetime) {
            $_SESSION = [];
            session_destroy();
            self::startFreshSession();
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    private static function startFreshSession(): void
    {
        session_name('societyos_session');
        session_set_cookie_params([
            'lifetime' => (int) config()['session_lifetime'] * 60,
            'path' => '/',
            'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}
