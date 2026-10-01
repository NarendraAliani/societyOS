<?php

declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../../public/static/css/app.css');
$reports = file_get_contents(__DIR__ . '/../../app/Views/reports/index.php');

$contracts = [
    '[data-bs-theme="dark"] .text-dark',
    'color: var(--bs-body-color) !important',
    '.report-card-heading',
    'display: flex',
    'align-items: center',
];

foreach ($contracts as $contract) {
    if (strpos($css, $contract) === false) {
        fwrite(STDERR, "Missing theme/report CSS contract: {$contract}\n");
        exit(1);
    }
}

if (strpos($reports, 'report-card-heading') === false || strpos($reports, 'report-card-link') === false) {
    fwrite(STDERR, "Report cards are missing the theme-aware heading/link classes.\n");
    exit(1);
}

if (strpos($reports, 'text-dark') !== false) {
    fwrite(STDERR, "Report directory still contains a hard-coded text-dark heading.\n");
    exit(1);
}

echo "Theme readability and report card regression contract passed.\n";
