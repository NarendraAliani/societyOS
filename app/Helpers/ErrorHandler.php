<?php

declare(strict_types=1);

namespace App\Helpers;

final class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler(static function (\Throwable $e): void {
            self::log($e);
            http_response_code(500);

            if ((bool) (config()['debug'] ?? false)) {
                echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
                return;
            }

            echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SocietyOS</title></head><body style="font-family:system-ui,sans-serif;padding:3rem;text-align:center"><h1>Something went wrong</h1><p>Please try again.</p><a href="/dashboard">Back to Dashboard</a></body></html>';
        });
    }

    private static function log(\Throwable $e): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        $line = sprintf(
            "[%s] %s in %s:%d\n%s\n\n",
            date('c'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
