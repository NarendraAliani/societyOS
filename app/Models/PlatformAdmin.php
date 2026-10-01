<?php

declare(strict_types=1);

namespace App\Models;

final class PlatformAdmin
{
    public static function findByEmail(string $email): ?array
    {
        $stmt=db()->prepare('SELECT * FROM platform_admins WHERE email=:email LIMIT 1');
        $stmt->execute(['email'=>$email]); return $stmt->fetch() ?: null;
    }
    public static function updatePassword(int $id,string $password): void
    {
        db()->prepare('UPDATE platform_admins SET password_hash=:hash,must_change_password=0 WHERE id=:id')
            ->execute(['hash'=>password_hash($password,PASSWORD_BCRYPT),'id'=>$id]);
    }
    public static function recordLogin(int $id): void { db()->prepare('UPDATE platform_admins SET last_login_at=NOW() WHERE id=:id')->execute(['id'=>$id]); }
    public static function logLoginHistory(?int $adminId,string $email,string $status): void
    {
        db()->prepare('INSERT INTO platform_login_history(platform_admin_id,email_attempted,ip_address,user_agent,status) VALUES(:admin_id,:email,:ip,:agent,:status)')
            ->execute(['admin_id'=>$adminId,'email'=>$email,'ip'=>$_SERVER['REMOTE_ADDR']??null,'agent'=>$_SERVER['HTTP_USER_AGENT']??null,'status'=>$status]);
    }
    public static function recentLoginAttempts(string $email): int
    {
        $stmt=db()->prepare('SELECT COUNT(*) FROM platform_login_history WHERE email_attempted=:email AND status="failed" AND created_at>(NOW()-INTERVAL 900 SECOND)');
        $stmt->execute(['email'=>$email]); return (int)$stmt->fetchColumn();
    }
}
