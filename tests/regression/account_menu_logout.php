<?php

declare(strict_types=1);

$layout = file_get_contents(__DIR__ . '/../../app/Views/layouts/app.php');

$required = [
    'action="/logout"',
    'method="post"',
    'Csrf::field()',
    'Logout',
];

foreach ($required as $needle) {
    if (strpos($layout, $needle) === false) {
        fwrite(STDERR, "Authenticated account menu logout contract missing: {$needle}\n");
        exit(1);
    }
}

echo "Account menu logout regression contract passed.\n";
