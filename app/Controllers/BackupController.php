<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Services\BackupService;

/**
 * Backup/restore is the single most destructive action in the app — restore overwrites the
 * whole database. Hard-restricted to super_admin in code (not just a grantable permission
 * key), so it can never end up in a lower-privilege role's permission set by mistake.
 */
final class BackupController
{
    private const BACKUP_DIR = __DIR__ . '/../../storage/backups';
    private const FILENAME_PATTERN = '/^backup_[0-9]{8}_[0-9]{6}(_pre_restore)?\.sql$/';

    public function index(): void
    {
        $this->requireSuperAdmin();

        $pageTitle = 'Backup & Restore';
        $backups = $this->listBackups();
        require __DIR__ . '/../Views/admin/backup.php';
    }

    public function create(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrf();

        $filename = $this->writeBackup();

        ActivityLog::log('admin', 'backup_create', "Created backup \"{$filename}\"");
        Flash::set('success', "Backup \"{$filename}\" created.");
        header('Location: /admin/backup');
        exit;
    }

    public function download(string $filename): void
    {
        $this->requireSuperAdmin();

        $path = $this->resolveBackupPath($filename);
        if (!$path) {
            http_response_code(404);
            exit('Not found.');
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function delete(string $filename): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrf();

        $path = $this->resolveBackupPath($filename);
        if ($path) {
            unlink($path);
            ActivityLog::log('admin', 'backup_delete', "Deleted backup \"{$filename}\"");
            Flash::set('success', 'Backup deleted.');
        }

        header('Location: /admin/backup');
        exit;
    }

    public function restoreFromList(string $filename): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrf();

        $path = $this->resolveBackupPath($filename);
        if (!$path) {
            Flash::set('error', 'Backup file not found.');
            header('Location: /admin/backup');
            exit;
        }

        $this->runRestore(file_get_contents($path), "stored backup \"{$filename}\"");
    }

    public function restoreFromUpload(): void
    {
        $this->requireSuperAdmin();
        $this->verifyCsrf();

        $file = $_FILES['restore_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            Flash::set('error', 'A valid .sql backup file is required.');
            header('Location: /admin/backup');
            exit;
        }

        if (!str_ends_with(strtolower($file['name']), '.sql')) {
            Flash::set('error', 'The uploaded file must be a .sql backup.');
            header('Location: /admin/backup');
            exit;
        }

        $this->runRestore(file_get_contents($file['tmp_name']), 'an uploaded file "' . basename($file['name']) . '"');
    }

    private function runRestore(string|false $sqlDump, string $sourceLabel): void
    {
        if ($sqlDump === false || trim($sqlDump) === '') {
            Flash::set('error', 'Could not read the backup file.');
            header('Location: /admin/backup');
            exit;
        }

        // Restore is irreversible if it goes wrong — always take a fresh safety backup of
        // the current state first, so a bad restore is itself recoverable.
        $safetyFilename = $this->writeBackup('_pre_restore');

        try {
            BackupService::restore($sqlDump);
        } catch (\Throwable $e) {
            ActivityLog::log('admin', 'restore_failed', "Restore from {$sourceLabel} failed: " . $e->getMessage());
            Flash::set('error', "Restore failed: {$e->getMessage()}. A safety backup of the prior state was saved as \"{$safetyFilename}\" before the restore ran — restore from it to recover.");
            header('Location: /admin/backup');
            exit;
        }

        // Restoring an older backup can mean the logged-in user's own row no longer exists
        // post-restore, which would fail this insert's FK — never let that hide the fact
        // that the restore itself succeeded.
        try {
            ActivityLog::log('admin', 'restore', "Restored database from {$sourceLabel} (safety backup: \"{$safetyFilename}\")");
        } catch (\Throwable $e) {
            // Restore succeeded; only the log entry failed. Nothing more to do here.
        }

        Flash::set('success', "Database restored from {$sourceLabel}. A safety backup of the prior state was saved as \"{$safetyFilename}\". If anything in the app looks wrong (login errors, missing records), your session may reference data from before the restore — log out and back in.");
        header('Location: /admin/backup');
        exit;
    }

    private function writeBackup(string $suffix = ''): string
    {
        if (!is_dir(self::BACKUP_DIR)) {
            mkdir(self::BACKUP_DIR, 0755, true);
        }

        $filename = 'backup_' . date('Ymd_His') . $suffix . '.sql';
        file_put_contents(self::BACKUP_DIR . '/' . $filename, BackupService::dump());

        return $filename;
    }

    private function listBackups(): array
    {
        if (!is_dir(self::BACKUP_DIR)) {
            return [];
        }

        $files = glob(self::BACKUP_DIR . '/*.sql') ?: [];
        $backups = array_map(fn ($path) => [
            'filename' => basename($path),
            'size' => filesize($path),
            'created_at' => date('Y-m-d H:i:s', filemtime($path)),
        ], $files);

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * Only ever accepts filenames matching the pattern this controller itself generates —
     * never a raw user-supplied path — so there's no path traversal surface even though the
     * filename comes from the URL/form.
     */
    private function resolveBackupPath(string $filename): ?string
    {
        if (!preg_match(self::FILENAME_PATTERN, $filename)) {
            return null;
        }

        $path = self::BACKUP_DIR . '/' . $filename;
        return is_file($path) ? $path : null;
    }

    private function requireSuperAdmin(): void
    {
        if (Auth::role() !== 'super_admin') {
            http_response_code(403);
            exit('Forbidden. Backup and restore is restricted to super_admin.');
        }
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
