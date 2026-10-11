<?php
/**
 * Global UI/layout contract.
 *
 * Every authenticated SocietyOS module view must render through the shared
 * app layout so theme, navigation, typography, accessibility and global JS/CSS
 * remain consistent. Standalone PDF/auth/error/platform views are explicitly
 * excluded because they have their own rendering shell.
 */
declare(strict_types=1);

$root = realpath(__DIR__ . '/../../app/Views');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve app/Views.\n");
    exit(1);
}

$standalone = [
    'auth/',
    'errors/',
    'landing/',
    'components/',
    'layouts/',
    'platform/',
    'billing/receipt_pdf.php',
    'reports/pdf_table.php',
];

$failures = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($root) + 1));

    $excluded = false;
    foreach ($standalone as $prefix) {
        if ($prefix !== '' && (str_ends_with($prefix, '/') ? str_starts_with($relative, $prefix) : $relative === $prefix)) {
            $excluded = true;
            break;
        }
    }
    if ($excluded) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    if ($content === false || strpos($content, "layouts/app.php") === false) {
        $failures[] = $relative . ' must use layouts/app.php';
    }

    if (strpos($content, 'AppHelpersCsrf::') !== false) {
        $failures[] = $relative . ' contains invalid AppHelpersCsrf helper syntax';
    }
}

$layout = file_get_contents($root . '/layouts/app.php');
if ($layout === false) {
    $failures[] = 'layouts/app.php cannot be read';
} else {
    foreach ([
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        '/static/css/app.css',
        'data-bs-theme',
        'data-font-size',
    ] as $required) {
        if (strpos($layout, $required) === false) {
            $failures[] = 'layouts/app.php missing global styling contract: ' . $required;
        }
    }
}

if ($failures) {
    fwrite(STDERR, "Global UI/layout contract failed:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

echo "Global UI/layout contract passed.\n";
