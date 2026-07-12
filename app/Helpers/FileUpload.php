<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Validates and stores an uploaded file under uploads/ (outside the web root — see
 * public/.htaccess and the FileServe pattern used by controllers; nothing under uploads/
 * is ever linked to directly, only streamed through an authenticated route).
 *
 * Security notes:
 * - Trusts finfo_file() (actual file content) for the MIME type, never the client-supplied
 *   $_FILES[...]['type'] or the original filename's extension — both are attacker-controlled.
 * - Generates a random filename; the original filename is discarded entirely.
 * - Uses move_uploaded_file(), which only succeeds on a file that genuinely arrived via
 *   an HTTP upload (blocks local-file-inclusion-style tricks with a crafted path).
 */
final class FileUpload
{
    private const IMAGE_MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    private const DOCUMENT_MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
    ];

    /** @return string|null relative path under uploads/, or null if no file was submitted */
    public static function storeImage(array $file, string $subdir): ?string
    {
        return self::store($file, $subdir, self::IMAGE_MIME_MAP);
    }

    /** @return string|null relative path under uploads/, or null if no file was submitted */
    public static function storeDocument(array $file, string $subdir): ?string
    {
        return self::store($file, $subdir, self::DOCUMENT_MIME_MAP);
    }

    /**
     * @throws \RuntimeException on an invalid, oversized, or wrong-type upload
     */
    private static function store(array $file, string $subdir, array $allowedMimeMap): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed. Please try again.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }

        $maxSizeMb = (int) config()['upload_max_size_mb'];
        if ($file['size'] > $maxSizeMb * 1024 * 1024) {
            throw new \RuntimeException("File is too large (max {$maxSizeMb} MB).");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMimeMap[$mime])) {
            throw new \RuntimeException('Unsupported file type.');
        }

        $extension = $allowedMimeMap[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destinationDir = dirname(__DIR__, 2) . '/uploads/' . $subdir;

        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
            throw new \RuntimeException('Could not prepare upload directory.');
        }

        if (!move_uploaded_file($file['tmp_name'], $destinationDir . '/' . $filename)) {
            throw new \RuntimeException('Could not save uploaded file.');
        }

        return $subdir . '/' . $filename;
    }
}
