<?php

declare(strict_types=1);

$composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);
$qr = file_get_contents(__DIR__ . '/../../app/Helpers/QrCode.php');
$visitor = file_get_contents(__DIR__ . '/../../app/Controllers/VisitorController.php');
$layout = file_get_contents(__DIR__ . '/../../app/Views/visitors/passes.php');

$checks = [
    'QR dependency declared' => isset($composer['require']['bacon/bacon-qr-code']),
    'QR helper uses SVG backend' => str_contains($qr, 'SvgImageBackEnd'),
    'QR helper writes content' => str_contains($qr, 'writeString'),
    'visitor pass page can render QR' => str_contains($visitor, 'QrCode::svg'),
    'visitor pass page exposes QR action' => str_contains($layout, 'show_qr='),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}

if ($failed) {
    fwrite(STDERR, "\nVisitor QR regression detected.\n");
    exit(1);
}

echo "Visitor QR regression contract passed.\n";
