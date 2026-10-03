<?php

declare(strict_types=1);

$entry = file_get_contents(__DIR__ . '/../../public/index.php');
$session = file_get_contents(__DIR__ . '/../../app/Helpers/Session.php');
$router = file_get_contents(__DIR__ . '/../../app/Helpers/Router.php');
$error = file_get_contents(__DIR__ . '/../../app/Helpers/ErrorHandler.php');
$layout = file_get_contents(__DIR__ . '/../../app/Views/layouts/app.php');
$upload = file_get_contents(__DIR__ . '/../../app/Helpers/FileUpload.php');
$schema = file_get_contents(__DIR__ . '/../../database/schema.sql');

$checks = [
    'content security policy' => str_contains($entry, 'Content-Security-Policy'),
    'strict session mode' => str_contains($session, 'session.use_strict_mode'),
    'cookie-only sessions' => str_contains($session, 'session.use_only_cookies'),
    'exception handler registered' => str_contains($entry, 'ErrorHandler::register()'),
    'exception handler logs' => str_contains($error, 'storage/logs'),
    'authenticated POST audit logging' => str_contains($router, "ActivityLog::log('route', 'POST'"),
    'destructive confirmation' => str_contains($layout, 'Delete this item?'),
    'role removal confirmation' => str_contains($layout, 'Remove this role assignment?'),
    'password reset confirmation' => str_contains($layout, 'Reset this user password?'),
    'responsive table handling' => str_contains($layout, 'table-responsive'),
    'long table search' => str_contains($layout, 'Search this table'),
    'upload MIME inspection' => str_contains($upload, 'finfo_file'),
    'upload random filename' => str_contains($upload, 'random_bytes'),
    'performance indexes in schema' => str_contains($schema, 'idx_bills_society_due_status'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}

if ($failed) {
    fwrite(STDERR, "\nQuality hardening regression detected.\n");
    exit(1);
}

echo "Quality hardening regression contract passed.\n";
