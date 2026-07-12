<?php

declare(strict_types=1);

namespace App\Models;

final class Role
{
    public static function all(): array
    {
        $stmt = db()->query('SELECT * FROM roles ORDER BY name');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM roles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function allPermissions(): array
    {
        $stmt = db()->query('SELECT * FROM permissions ORDER BY module, `key`');
        return $stmt->fetchAll();
    }

    public static function permissionIdsForRole(int $roleId): array
    {
        $stmt = db()->prepare('SELECT permission_id FROM role_permissions WHERE role_id = :role_id');
        $stmt->execute(['role_id' => $roleId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'permission_id'));
    }

    /**
     * Replaces a role's permission set atomically with the given permission IDs.
     */
    public static function setPermissions(int $roleId, array $permissionIds): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id')->execute(['role_id' => $roleId]);

            $insert = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
            foreach ($permissionIds as $permissionId) {
                $insert->execute(['role_id' => $roleId, 'permission_id' => (int) $permissionId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
