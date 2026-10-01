<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Mailer;
use App\Models\PlatformAdmin;
use App\Models\User;

final class PasswordResetService
{
    private const TOKEN_TTL_MINUTES = 60;
    private const MAX_REQUESTS_PER_HOUR = 5;

    public static function requestUserReset(int $societyId, string $email): void
    {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || self::tooManyRequests('password_resets', $email, $societyId)) {
            return;
        }

        $user = User::findByEmail($societyId, $email);
        if (!$user || ($user['status'] ?? '') !== 'active') {
            return;
        }

        $token = self::issueToken('password_resets', 'user_id', (int) $user['id']);
        $url = self::baseUrl() . '/reset-password?token=' . rawurlencode($token) . '&type=user';

        try {
            Mailer::sendPasswordReset($user['email'], $user['name'], $url, 'account');
        } catch (\Throwable $e) {
            error_log('SocietyOS password reset email failed: ' . $e->getMessage());
        }
    }

    public static function requestPlatformReset(string $email): void
    {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || self::tooManyRequests('platform_password_resets', $email)) {
            return;
        }

        $admin = PlatformAdmin::findByEmail($email);
        if (!$admin || ($admin['status'] ?? '') !== 'active') {
            return;
        }

        $token = self::issueToken('platform_password_resets', 'platform_admin_id', (int) $admin['id'], $email);
        $url = self::baseUrl() . '/platform/reset-password?token=' . rawurlencode($token) . '&type=platform';

        try {
            Mailer::sendPasswordReset($admin['email'], $admin['name'], $url, 'platform');
        } catch (\Throwable $e) {
            error_log('SocietyOS platform password reset email failed: ' . $e->getMessage());
        }
    }

    public static function validateToken(string $token, string $type): ?array
    {
        if ($token === '' || !in_array($type, ['user', 'platform'], true)) {
            return null;
        }

        $table = $type === 'platform' ? 'platform_password_resets' : 'password_resets';
        $ownerColumn = $type === 'platform' ? 'platform_admin_id' : 'user_id';

        $stmt = db()->prepare(
            "SELECT * FROM {$table}
             WHERE token_hash=:hash AND used_at IS NULL AND expires_at>NOW()
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
        $reset = $stmt->fetch();

        if (!$reset) {
            return null;
        }

        $ownerId = (int) $reset[$ownerColumn];
        $owner = $type === 'platform' ? PlatformAdmin::findById($ownerId) : User::find($ownerId);

        if (!$owner || ($owner['status'] ?? '') !== 'active') {
            return null;
        }

        return ['reset' => $reset, 'owner' => $owner, 'type' => $type];
    }

    public static function completeReset(string $token, string $type, string $newPassword): bool
    {
        $validated = self::validateToken($token, $type);
        if (!$validated) {
            return false;
        }

        $table = $type === 'platform' ? 'platform_password_resets' : 'password_resets';
        $ownerColumn = $type === 'platform' ? 'platform_admin_id' : 'user_id';
        $ownerId = (int) $validated['owner']['id'];
        $pdo = db();

        $pdo->beginTransaction();
        try {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);

            if ($type === 'platform') {
                $pdo->prepare('UPDATE platform_admins SET password_hash=:hash,must_change_password=0 WHERE id=:id')
                    ->execute(['hash' => $hash, 'id' => $ownerId]);
            } else {
                $pdo->prepare('UPDATE users SET password_hash=:hash,must_change_password=0 WHERE id=:id')
                    ->execute(['hash' => $hash, 'id' => $ownerId]);
                $pdo->prepare('DELETE FROM user_sessions WHERE user_id=:id')->execute(['id' => $ownerId]);
            }

            $pdo->prepare("UPDATE {$table} SET used_at=NOW() WHERE id=:id AND {$ownerColumn}=:owner_id AND used_at IS NULL")
                ->execute(['id' => (int) $validated['reset']['id'], 'owner_id' => $ownerId]);

            $pdo->prepare("DELETE FROM {$table} WHERE {$ownerColumn}=:owner_id AND used_at IS NULL AND id!=:id")
                ->execute(['owner_id' => $ownerId, 'id' => (int) $validated['reset']['id']]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function issueToken(string $table, string $ownerColumn, int $ownerId, ?string $email = null): string
    {
        $token = bin2hex(random_bytes(32));

        if ($table === 'platform_password_resets') {
            $stmt = db()->prepare(
                "INSERT INTO {$table} ({$ownerColumn},email_attempted,token_hash,expires_at)
                 VALUES(:owner_id,:email,:hash,DATE_ADD(NOW(),INTERVAL " . self::TOKEN_TTL_MINUTES . " MINUTE))"
            );
            $stmt->execute(['owner_id' => $ownerId, 'email' => $email, 'hash' => hash('sha256', $token)]);
            return $token;
        }

        $stmt = db()->prepare(
            "INSERT INTO {$table} ({$ownerColumn},token_hash,expires_at)
             VALUES(:owner_id,:hash,DATE_ADD(NOW(),INTERVAL " . self::TOKEN_TTL_MINUTES . " MINUTE))"
        );
        $stmt->execute(['owner_id' => $ownerId, 'hash' => hash('sha256', $token)]);
        return $token;
    }

    private static function tooManyRequests(string $table, string $email, ?int $societyId = null): bool
    {
        if ($table === 'platform_password_resets') {
            $stmt = db()->prepare(
                "SELECT COUNT(*) FROM {$table}
                 WHERE email_attempted=:email AND created_at>DATE_SUB(NOW(),INTERVAL 1 HOUR)"
            );
            $stmt->execute(['email' => $email]);
            return (int) $stmt->fetchColumn() >= self::MAX_REQUESTS_PER_HOUR;
        }

        $stmt = db()->prepare(
            "SELECT COUNT(*) FROM {$table} pr
             JOIN users u ON u.id=pr.user_id
             WHERE u.society_id=:sid AND u.email=:email
               AND pr.created_at>DATE_SUB(NOW(),INTERVAL 1 HOUR)"
        );
        $stmt->execute(['sid' => $societyId, 'email' => $email]);
        return (int) $stmt->fetchColumn() >= self::MAX_REQUESTS_PER_HOUR;
    }

    private static function baseUrl(): string
    {
        return rtrim((string) config()['url'], '/');
    }
}
