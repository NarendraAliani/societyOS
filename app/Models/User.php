<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public static function findByEmail(int $societyId, string $email): ?array
    {
        $stmt = db()->prepare(
            'SELECT u.*, r.name AS role_name,
                    m.name AS linked_member_name,
                    m.member_type AS linked_member_type,
                    f.flat_number AS linked_flat_number,
                    w.name AS linked_wing_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN members m ON m.id = u.member_id
             LEFT JOIN flats f ON f.id = m.flat_id
             LEFT JOIN floors fl ON fl.id = f.floor_id
             LEFT JOIN wings w ON w.id = fl.wing_id
             WHERE u.society_id = :society_id AND u.email = :email
             LIMIT 1'
        );
        $stmt->execute(['society_id' => $societyId, 'email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function permissionsForRole(int $roleId): array
    {
        $stmt = db()->prepare(
            'SELECT p.`key` FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(), 'key');
    }

    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT u.*, r.name AS role_name,
                    m.name AS linked_member_name,
                    m.member_type AS linked_member_type,
                    f.flat_number AS linked_flat_number,
                    w.name AS linked_wing_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN members m ON m.id = u.member_id
             LEFT JOIN flats f ON f.id = m.flat_id
             LEFT JOIN floors fl ON fl.id = f.floor_id
             LEFT JOIN wings w ON w.id = fl.wing_id
             WHERE u.society_id = :sid
             ORDER BY u.name'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExists(int $societyId, string $email, ?int $excludingUserId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE society_id = :sid AND email = :email';
        $params = ['sid' => $societyId, 'email' => $email];

        if ($excludingUserId !== null) {
            $sql .= ' AND id != :excluding_id';
            $params['excluding_id'] = $excludingUserId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function updateOwnProfile(int $id, string $name, string $email, ?string $phone): void
    {
        $stmt = db()->prepare('UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id');
        $stmt->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'id' => $id]);
    }

    public static function create(int $societyId, string $name, string $email, ?string $phone, int $roleId, string $password, ?int $memberId = null): int
    {
        $stmt = db()->prepare(
            'INSERT INTO users (society_id, role_id, member_id, name, email, phone, password_hash, status, must_change_password)
             VALUES (:sid, :role_id, :member_id, :name, :email, :phone, :hash, "active", 1)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'role_id' => $roleId,
            'member_id' => $memberId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        return (int) db()->lastInsertId();
    }

    public static function updateRoleAndStatus(int $id, int $roleId, string $status, ?int $memberId = null): void
    {
        $stmt = db()->prepare('UPDATE users SET role_id = :role_id, status = :status, member_id = :member_id WHERE id = :id');
        $stmt->execute(['role_id' => $roleId, 'status' => $status, 'member_id' => $memberId, 'id' => $id]);
    }

    public static function memberBelongsToSociety(int $memberId, int $societyId): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM members WHERE id = :id AND society_id = :sid AND status = "active"');
        $stmt->execute(['id' => $memberId, 'sid' => $societyId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function memberEligibleForRole(int $memberId, int $societyId, int $roleId): bool
    {
        $role = Role::find($roleId);
        if (!$role || !in_array($role['name'], ['resident', 'tenant'], true)) {
            return false;
        }

        $memberType = $role['name'] === 'tenant' ? 'tenant' : 'owner';
        $stmt = db()->prepare(
            'SELECT COUNT(*)
             FROM members
             WHERE id = :id
               AND society_id = :sid
               AND status = "active"
               AND member_type = :member_type'
        );
        $stmt->execute([
            'id' => $memberId,
            'sid' => $societyId,
            'member_type' => $memberType,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * A member may have only one user account for a given role.
     * The same member may still be assigned a different role.
     */
    public static function memberRoleIsLinked(int $memberId, int $societyId, int $roleId, ?int $excludingUserId = null): bool
    {
        $sql = 'SELECT COUNT(*)
                FROM users
                WHERE society_id = :sid
                  AND role_id = :role_id
                  AND member_id = :member_id';
        $params = [
            'sid' => $societyId,
            'role_id' => $roleId,
            'member_id' => $memberId,
        ];

        if ($excludingUserId !== null) {
            $sql .= ' AND id != :excluding_id';
            $params['excluding_id'] = $excludingUserId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Return members who are available for the selected role only.
     * Existing links for other roles do not remove the member from this list.
     */
    public static function availableMembersForRole(int $societyId, int $roleId, ?int $excludingUserId = null): array
    {
        $role = Role::find($roleId);
        if (!$role || !in_array($role['name'], ['resident', 'tenant'], true)) {
            return [];
        }

        $memberType = $role['name'] === 'tenant' ? 'tenant' : 'owner';

        $sql = 'SELECT m.id, m.name, m.member_type, m.email, m.phone,
                       f.flat_number, w.name AS wing_name
                FROM members m
                JOIN flats f ON f.id = m.flat_id
                JOIN floors fl ON fl.id = f.floor_id
                JOIN wings w ON w.id = fl.wing_id
                WHERE m.society_id = :sid
                  AND m.status = "active"
                  AND m.member_type = :member_type
                  AND NOT EXISTS (
                      SELECT 1
                      FROM users u
                      WHERE u.society_id = :linked_sid
                        AND u.role_id = :role_id
                        AND u.member_id = m.id';

        $params = [
            'sid' => $societyId,
            'member_type' => $memberType,
            'linked_sid' => $societyId,
            'role_id' => $roleId,
        ];

        if ($excludingUserId !== null) {
            $sql .= ' AND u.id != :excluding_id';
            $params['excluding_id'] = $excludingUserId;
        }

        $sql .= ')
                ORDER BY w.name, f.flat_number, m.name';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function resetPassword(int $id, string $newPassword): void
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = :hash, must_change_password = 1 WHERE id = :id');
        $stmt->execute(['hash' => password_hash($newPassword, PASSWORD_BCRYPT), 'id' => $id]);
    }

    public static function changeOwnPassword(int $id, string $newPassword): void
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = :hash, must_change_password = 0 WHERE id = :id');
        $stmt->execute(['hash' => password_hash($newPassword, PASSWORD_BCRYPT), 'id' => $id]);
    }

    public static function recordLogin(int $userId): void
    {
        $stmt = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public static function logLoginHistory(?int $userId, string $emailAttempted, string $status): void
    {
        $stmt = db()->prepare(
            'INSERT INTO login_history (user_id, email_attempted, ip_address, user_agent, status)
             VALUES (:user_id, :email, :ip, :agent, :status)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'email' => $emailAttempted,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'status' => $status,
        ]);
    }
}
