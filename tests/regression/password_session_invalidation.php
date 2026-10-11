<?php

declare(strict_types=1);

$user = file_get_contents(__DIR__ . '/../../app/Models/User.php');

$checks = [
    'admin password reset invalidates tracked sessions' => str_contains($user, "must_change_password=1 WHERE id=:id") && str_contains($user, "DELETE FROM user_sessions WHERE user_id=:id"),
    'self-service password change invalidates tracked sessions' => str_contains($user, "must_change_password=0 WHERE id=:id") && substr_count($user, "DELETE FROM user_sessions WHERE user_id=:id") >= 2,
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}

if ($failed) {
    fwrite(STDERR, "\nPassword session invalidation regression detected.\n");
    exit(1);
}

echo "Password session invalidation regression contract passed.\n";
