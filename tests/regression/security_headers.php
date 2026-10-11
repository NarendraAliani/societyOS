<?php

declare(strict_types=1);

$entry = file_get_contents(__DIR__ . '/../../public/index.php');

$checks = [
    'content type sniffing protection' => str_contains($entry, "header('X-Content-Type-Options: nosniff')"),
    'clickjacking protection' => str_contains($entry, "header('X-Frame-Options: SAMEORIGIN')"),
    'referrer policy' => str_contains($entry, "header('Referrer-Policy: strict-origin-when-cross-origin')"),
    'permissions policy' => str_contains($entry, "header('Permissions-Policy: geolocation=(), microphone=(), camera=()')"),
    'HTTPS transport security' => str_contains($entry, "header('Strict-Transport-Security: max-age=31536000; includeSubDomains')"),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}

if ($failed) {
    fwrite(STDERR, "\nSecurity header regression detected.\n");
    exit(1);
}

echo "Security header regression contract passed.\n";
