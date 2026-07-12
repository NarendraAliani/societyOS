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
        session_start();

        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = time();
        } elseif (time() - $_SESSION['_last_activity'] > $lifetime) {
            $_SESSION = [];
            session_destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
