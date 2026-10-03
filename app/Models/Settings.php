<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Per-society key/value overrides on top of the .env-backed config() defaults. A setting
 * with no row here simply falls back to whatever the caller passes as $default (normally
 * config()[$key]) — so an empty settings table behaves exactly like before this feature
 * existed.
 */
final class Settings
{
    public static function get(int $societyId, string $key, mixed $default = null): mixed
    {
        $stmt = db()->prepare('SELECT `value` FROM settings WHERE society_id = :sid AND `key` = :key');
        $stmt->execute(['sid' => $societyId, 'key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    }

    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT `key`, `value` FROM settings WHERE society_id = :sid');
        $stmt->execute(['sid' => $societyId]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['key']] = $row['value'];
        }
        return $rows;
    }

    public static function set(int $societyId, string $key, string $value): void
    {
        $stmt = db()->prepare(
            'INSERT INTO settings (society_id, `key`, `value`) VALUES (:sid, :key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute(['sid' => $societyId, 'key' => $key, 'value' => $value]);
    }
}
