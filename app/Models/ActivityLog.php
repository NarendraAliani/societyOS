<?php

declare(strict_types=1);

namespace App\Models;

final class ActivityLog
{
    public static function log(string $module, string $action, ?string $description = null): void
    {
        $stmt = db()->prepare(
            'INSERT INTO activity_logs (society_id, user_id, module, action, description, ip_address)
             VALUES (:society_id, :user_id, :module, :action, :description, :ip_address)'
        );
        $stmt->execute([
            'society_id' => $_SESSION['society_id'] ?? 0,
            'user_id' => \App\Helpers\Auth::id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public static function recent(int $societyId, int $limit = 200): array
    {
        $stmt = db()->prepare(
            'SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.society_id = :sid
             ORDER BY al.created_at DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function loginHistory(int $limit = 200): array
    {
        $stmt = db()->query(
            'SELECT lh.*, u.name AS user_name
             FROM login_history lh
             LEFT JOIN users u ON u.id = lh.user_id
             ORDER BY lh.created_at DESC
             LIMIT ' . (int) $limit
        );
        return $stmt->fetchAll();
    }
}
