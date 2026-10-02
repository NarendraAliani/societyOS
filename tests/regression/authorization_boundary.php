<?php

declare(strict_types=1);

$routes = file_get_contents(__DIR__ . '/../../public/index.php');
$controllers = glob(__DIR__ . '/../../app/Controllers/*.php');

$publicPost = [
    "'/login'",
    "'/forgot-password'",
    "'/reset-password'",
    "'/platform/login'",
    "'/platform/forgot-password'",
    "'/platform/reset-password'",
    "'/logout'",
];

$failures = [];
foreach (preg_split('/\R/', $routes) as $line) {
    if (!str_contains($line, '$router->post(')) {
        continue;
    }

    $isPublic = false;
    foreach ($publicPost as $route) {
        if (str_contains($line, $route)) {
            $isPublic = true;
            break;
        }
    }

    if (!$isPublic && !str_contains($line, '[$auth') && !str_contains($line, 'PlatformAdminMiddleware')) {
        $failures[] = 'Unprotected POST route: ' . trim($line);
    }
}

foreach ($controllers as $controller) {
    $name = basename($controller);
    $code = file_get_contents($controller);
    if ($name === 'LandingController.php' || $name === 'DashboardController.php' || $name === 'ReportController.php') {
        continue;
    }
    if (str_contains($code, '$_POST') && !str_contains($code, 'Csrf::verify')) {
        $failures[] = $name . ' reads POST data without a CSRF verifier';
    }
}

if ($failures) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}

echo "Authorization boundary regression contract passed.\n";
