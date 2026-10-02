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

            $pageTitle = 'Something went wrong';
            $message = 'Something went wrong while processing your request. Please try again.';
            require __DIR__ . '/../Views/errors/500.php';
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
