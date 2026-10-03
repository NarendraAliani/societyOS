<?php
declare(strict_types=1);

$required = [
    'app/Helpers/Captcha.php',
    'app/Views/components/captcha.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/PlatformController.php',
    'app/Views/auth/login.php',
    'app/Views/platform/login.php',
];
foreach ($required as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing required CAPTCHA file: {$file}\n");
        exit(1);
    }
}

require_once 'app/Helpers/Captcha.php';

$_SESSION = [];
\App\Helpers\Captcha::refresh();
$challenge = $_SESSION['_captcha'] ?? null;
$value = (string) ($challenge['value'] ?? '');

$checks = [
    'challenge is 6 characters' => strlen($value) === 6,
    'challenge uses expected session hash' => !empty($challenge['hash']),
    'challenge expires in five minutes' => (int) ($challenge['expires_at'] ?? 0) >= time() + 299,
    'SVG is rendered' => str_contains(\App\Helpers\Captcha::svg(), '<svg'),
    'CAPTCHA accepts case-insensitive answer' => \App\Helpers\Captcha::verify(strtolower($value)),
    'CAPTCHA is one-time' => !\App\Helpers\Captcha::verify($value),
];

$authController = file_get_contents('app/Controllers/AuthController.php');
$platformController = file_get_contents('app/Controllers/PlatformController.php');
$authView = file_get_contents('app/Views/auth/login.php');
$platformView = file_get_contents('app/Views/platform/login.php');
$component = file_get_contents('app/Views/components/captcha.php');

$checks += [
    'user login verifies CAPTCHA' => str_contains($authController, 'Captcha::verify($_POST[\'captcha_code\'] ?? null)'),
    'platform login verifies CAPTCHA' => str_contains($platformController, 'Captcha::verify($_POST[\'captcha_code\'] ?? null)'),
    'user login includes shared CAPTCHA component' => str_contains($authView, "components/captcha.php"),
    'platform login includes shared CAPTCHA component' => str_contains($platformView, "components/captcha.php"),
    'shared CAPTCHA input is required' => str_contains($component, 'name="captcha_code"') && str_contains($component, 'required'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    if (!$passed) {
        $failed = true;
    }
}

if ($failed) {
    fwrite(STDERR, "\nAuthentication CAPTCHA regression detected.\n");
    exit(1);
}

echo "\nAll authentication CAPTCHA regression contracts passed.\n";
