<?php
declare(strict_types=1);

$required = [
    'app/Helpers/Mailer.php',
    'app/Services/PasswordResetService.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/PlatformController.php',
    'app/Views/auth/forgot_password.php',
    'app/Views/auth/reset_password.php',
    'app/Views/platform/forgot_password.php',
    'app/Views/platform/reset_password.php',
    'database/migrations/2026-10-01-platform-password-reset.sql',
];

$failed = false;
foreach ($required as $file) {
    $passed = is_file($file);
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', "required password-reset file: {$file}");
    $failed = $failed || !$passed;
}

$routes = file_get_contents('public/index.php');
$auth = file_get_contents('app/Controllers/AuthController.php');
$platform = file_get_contents('app/Controllers/PlatformController.php');
$service = file_get_contents('app/Services/PasswordResetService.php');
$mailer = file_get_contents('app/Helpers/Mailer.php');
$login = file_get_contents('app/Views/auth/login.php');
$platformLogin = file_get_contents('app/Views/platform/login.php');
$schema = file_get_contents('database/schema.sql');
$migration = file_get_contents('database/migrations/2026-10-01-platform-password-reset.sql');

$checks = [
    'user forgot route exists' => str_contains($routes, "get('/forgot-password'"),
    'user reset route exists' => str_contains($routes, "post('/reset-password'"),
    'platform forgot route exists' => str_contains($routes, "get('/platform/forgot-password'"),
    'platform reset route exists' => str_contains($routes, "post('/platform/reset-password'"),
    'user reset service uses hashed token' => str_contains($service, "hash('sha256', \\$token)"),
    'user reset token expires in 60 minutes' => str_contains($service, 'TOKEN_TTL_MINUTES = 60'),
    'reset token is one-time' => str_contains($service, 'used_at IS NULL') && str_contains($service, 'used_at=NOW()'),
    'reset requests are rate limited' => str_contains($service, 'MAX_REQUESTS_PER_HOUR = 5'),
    'reset request uses SMTP mailer' => str_contains($service, 'Mailer::sendPasswordReset'),
    'mailer uses SMTP' => str_contains($mailer, '$mail->isSMTP()'),
    'user login links to forgot password' => str_contains($login, 'href="/forgot-password"'),
    'platform login links to forgot password' => str_contains($platformLogin, 'href="/platform/forgot-password"'),
    'platform reset table in schema' => str_contains($schema, 'CREATE TABLE IF NOT EXISTS platform_password_resets'),
    'platform reset migration exists' => str_contains($migration, 'CREATE TABLE IF NOT EXISTS platform_password_resets'),
    'user reset invalidates tracked sessions' => str_contains($service, 'DELETE FROM user_sessions'),
    'user reset does not disclose account existence' => str_contains($auth, 'If an active account matches those details, a password reset link has been sent to the registered email address.'),
];

foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}

if ($failed) {
    fwrite(STDERR, "\nEmail password-reset regression detected.\n");
    exit(1);
}

echo "\nAll email password-reset regression contracts passed.\n";
