<?php

declare(strict_types=1);

$root = dirname(__DIR__);

if (file_exists($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}

if (class_exists(\Dotenv\Dotenv::class) && file_exists($root . '/.env')) {
    \Dotenv\Dotenv::createImmutable($root)->load();
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }
        return match (strtolower((string) $value)) {
            'true' => true,
            'false' => false,
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(): array
    {
        static $config = null;
        if ($config === null) {
            $config = [
                'name' => env('APP_NAME', 'SocietyOS'),
                'url' => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
                'env' => env('APP_ENV', 'production'),
                'debug' => (bool) env('APP_DEBUG', false),
                'key' => env('APP_KEY', ''),
                'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
                'upload_max_size_mb' => (int) env('UPLOAD_MAX_SIZE_MB', 5),
                'penalty_interest_rate_percent' => (float) env('PENALTY_INTEREST_RATE_PERCENT', 18.0),
                'mail' => [
                    'host' => env('MAIL_HOST'),
                    'port' => (int) env('MAIL_PORT', 587),
                    'username' => env('MAIL_USERNAME'),
                    'password' => env('MAIL_PASSWORD'),
                    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                    'from_address' => env('MAIL_FROM_ADDRESS'),
                    'from_name' => env('MAIL_FROM_NAME', 'SocietyOS'),
                ],
                'sms' => [
                    'provider' => env('SMS_PROVIDER'),
                    'api_key' => env('SMS_API_KEY'),
                    'sender_id' => env('SMS_SENDER_ID'),
                ],
            ];
        }
        return $config;
    }
}

return config();
