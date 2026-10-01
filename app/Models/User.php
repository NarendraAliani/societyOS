<?php
declare(strict_types=1);
namespace App\Models;

final class User
{
    public static function findByEmail(int $societyId, string $email): ?array
    {
        $stmt = db()->prepare('SELECT u.*, ur.id AS role_assignment_id, ur.role_id, ur.member_id, r.name AS role_name,
                    m.name AS linked_member_name, m.member_type AS linked_member_type,
                    f.flat_number AS linked_flat_number, w.name AS linked_wing_name
             FROM users u JOIN user_roles ur ON ur.user_id = u.id AND ur.is_default = 1
             JOIN roles r ON r.id = ur.role_id
             LEFT JOIN members m ON m.id = ur.member_id LEFT JOIN flats f ON f.id = ur.flat_id
             LEFT JOIN floors fl ON fl.id = f.floor_id LEFT JOIN wings w ON w.id = fl.wing_id
             WHERE u.society_id = :society_id AND u.email = :email LIMIT 1');
        $stmt->execute(['society_id'=>$societyId,'email'=>$email]);
        return $stmt->fetch() ?: null;
    }

    public static function permissionsForRole(int $roleId): array
    {
        $stmt=db()->prepare('SELECT p.key FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=:role_id');
        $stmt->execute(['role_id'=>$roleId]);
        return array_column($stmt->fetchAll(),'key');
    }

    public static function rolesForUser(int $userId): array
    {
        $stmt=db()->prepare('SELECT ur.id AS assignment_id, ur.role_id, ur.member_id, ur.flat_id, ur.is_default,
                    r.name AS role_name, m.name AS member_name, m.member_type, f.flat_number, w.name AS wing_name
             FROM user_roles ur JOIN roles r ON r.id=ur.role_id
             LEFT JOIN members m ON m.id=ur.member_id LEFT JOIN flats f ON f.id=ur.flat_id
             LEFT JOIN floors fl ON fl.id=f.floor_id LEFT JOIN wings w ON w.id=fl.wing_id
             WHERE ur.user_id=:user_id ORDER BY ur.is_default DESC,r.name');
        $stmt->execute(['user_id'=>$userId]); return $stmt->fetchAll();
    }

    public static function assignmentForUser(int $userId,int $roleId): ?array
    {
        $stmt=db()->prepare('SELECT ur.*,r.name AS role_name FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:user_id AND ur.role_id=:role_id LIMIT 1');
        $stmt->execute(['user_id'=>$userId,'role_id'=>$roleId]); return $stmt->fetch() ?: null;
    }

    public static function allForSociety(int $societyId): array
    {
        $stmt=db()->prepare('SELECT u.*, GROUP_CONCAT(DISTINCT r.name ORDER BY ur.is_default DESC,r.name SEPARATOR ", ") AS role_names,
                    d.role_id AS default_role_id,d.member_id AS default_member_id,d.flat_id AS default_flat_id,
                    dm.name AS default_member_name,df.flat_number AS default_flat_number,dw.name AS default_wing_name
             FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id
             LEFT JOIN user_roles d ON d.user_id=u.id AND d.is_default=1
             LEFT JOIN members dm ON dm.id=d.member_id LEFT JOIN flats df ON df.id=d.flat_id
             LEFT JOIN floors dfl ON dfl.id=df.floor_id LEFT JOIN wings dw ON dw.id=dfl.wing_id
             WHERE u.society_id=:sid
             GROUP BY u.id,d.role_id,d.member_id,d.flat_id,dm.name,df.flat_number,dw.name ORDER BY u.name');
        $stmt->execute(['sid'=>$societyId]); return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt=db()->prepare('SELECT u.*,ur.id AS role_assignment_id,ur.role_id,ur.member_id,r.name AS role_name,
                    m.name AS linked_member_name,m.member_type AS linked_member_type,f.flat_number AS linked_flat_number,w.name AS linked_wing_name
             FROM users u JOIN user_roles ur ON ur.user_id=u.id AND ur.is_default=1 JOIN roles r ON r.id=ur.role_id
             LEFT JOIN members m ON m.id=ur.member_id LEFT JOIN flats f ON f.id=ur.flat_id
             LEFT JOIN floors fl ON fl.id=f.floor_id LEFT JOIN wings w ON w.id=fl.wing_id WHERE u.id=:id LIMIT 1');
        $stmt->execute(['id'=>$id]); return $stmt->fetch() ?: null;
    }

    public static function emailExists(int $societyId,string $email,?int $excludingUserId=null): bool
    {
        $sql='SELECT COUNT(*) FROM users WHERE society_id=:sid AND email=:email'; $params=['sid'=>$societyId,'email'=>$email];
        if($excludingUserId!==null){$sql.=' AND id!=:excluding_id';$params['excluding_id']=$excludingUserId;}
        $stmt=db()->prepare($sql);$stmt->execute($params);return (int)$stmt->fetchColumn()>0;
    }

    public static function updateOwnProfile(int $id,string $name,string $email,?string $phone): void
    {
        $stmt=db()->prepare('UPDATE users SET name=:name,email=:email,phone=:phone WHERE id=:id');
        $stmt->execute(['name'=>$name,'email'=>$email,'phone'=>$phone,'id'=>$id]);
    }

    public static function create(int $societyId,string $name,string $email,?string $phone,int $roleId,string $password,int $memberId): int
    {
        $pdo=db();$pdo->beginTransaction();
        try{
            $stmt=$pdo->prepare('INSERT INTO users(society_id,role_id,member_id,name,email,phone,password_hash,status,must_change_password)
                VALUES(:sid,:role_id,:member_id,:name,:email,:phone,:hash,"active",1)');
            $stmt->execute(['sid'=>$societyId,'role_id'=>$roleId,'member_id'=>$memberId,'name'=>$name,'email'=>$email,'phone'=>$phone,'hash'=>password_hash($password,PASSWORD_BCRYPT)]);
            $userId=(int)$pdo->lastInsertId();
            $stmt=$pdo->prepare('INSERT INTO user_roles(user_id,society_id,role_id,member_id,flat_id,is_default)
                SELECT :uid,:sid,:rid,:mid,m.flat_id,1 FROM members m WHERE m.id=:mid2 AND m.society_id=:sid2');
            $stmt->execute(['uid'=>$userId,'sid'=>$societyId,'rid'=>$roleId,'mid'=>$memberId,'mid2'=>$memberId,'sid2'=>$societyId]);
            if($stmt->rowCount()!==1) throw new \InvalidArgumentException('Unable to create the role assignment.');
            $pdo->commit(); return $userId;
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }

    public static function roleAlreadyAssigned(int $userId,int $roleId): bool
    {
        $stmt=db()->prepare('SELECT COUNT(*) FROM user_roles WHERE user_id=:user_id AND role_id=:role_id');
        $stmt->execute(['user_id'=>$userId,'role_id'=>$roleId]);return (int)$stmt->fetchColumn()>0;
    }

    public static function addRole(int $userId,int $societyId,int $roleId,int $memberId): void
    {
        if(self::roleAlreadyAssigned($userId,$roleId)) throw new \InvalidArgumentException('That role is already assigned to this user.');
        $pdo=db();$pdo->beginTransaction();
        try{
            $stmt=$pdo->prepare('INSERT INTO user_roles(user_id,society_id,role_id,member_id,flat_id,is_default)
                SELECT :uid,:sid,:rid,:mid,m.flat_id,
                CASE WHEN NOT EXISTS(SELECT 1 FROM user_roles WHERE user_id=:duid) THEN 1 ELSE 0 END
                FROM members m WHERE m.id=:mid2 AND m.society_id=:sid2 AND m.status="active"');
            $stmt->execute(['uid'=>$userId,'sid'=>$societyId,'rid'=>$roleId,'mid'=>$memberId,'duid'=>$userId,'mid2'=>$memberId,'sid2'=>$societyId]);
            if($stmt->rowCount()!==1) throw new \InvalidArgumentException('The selected resident/member is not active in this society.');
            $pdo->commit();
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }

    public static function setDefaultRole(int $userId,int $roleId): void
    {
        if(!self::roleAlreadyAssigned($userId,$roleId)) throw new \InvalidArgumentException('That role is not assigned to this user.');
        $pdo=db();$pdo->beginTransaction();
        try{
            $pdo->prepare('UPDATE user_roles SET is_default=0 WHERE user_id=:uid')->execute(['uid'=>$userId]);
            $pdo->prepare('UPDATE user_roles SET is_default=1 WHERE user_id=:uid AND role_id=:rid')->execute(['uid'=>$userId,'rid'=>$roleId]);
            $pdo->commit();
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }

    public static function removeRole(int $userId,int $roleId): void
    {
        $stmt=db()->prepare('SELECT COUNT(*) FROM user_roles WHERE user_id=:uid');$stmt->execute(['uid'=>$userId]);
        if((int)$stmt->fetchColumn()<=1) throw new \InvalidArgumentException('A user must retain at least one role.');
        $assignment=self::assignmentForUser($userId,$roleId);
        if(!$assignment) throw new \InvalidArgumentException('That role is not assigned to this user.');
        $pdo=db();$pdo->beginTransaction();
        try{
            $pdo->prepare('DELETE FROM user_roles WHERE user_id=:uid AND role_id=:rid')->execute(['uid'=>$userId,'rid'=>$roleId]);
            if(!empty($assignment['is_default'])) $pdo->prepare('UPDATE user_roles SET is_default=1 WHERE user_id=:uid ORDER BY id LIMIT 1')->execute(['uid'=>$userId]);
            $pdo->commit();
        }catch(\Throwable $e){$pdo->rollBack();throw $e;}
    }

    public static function updateStatus(int $id,string $status): void
    {
        $stmt=db()->prepare('UPDATE users SET status=:status WHERE id=:id');$stmt->execute(['status'=>$status,'id'=>$id]);
    }

    public static function memberBelongsToSociety(int $memberId,int $societyId): bool
    {
        $stmt=db()->prepare('SELECT COUNT(*) FROM members WHERE id=:id AND society_id=:sid AND status="active"');
        $stmt->execute(['id'=>$memberId,'sid'=>$societyId]);return (int)$stmt->fetchColumn()>0;
    }

    public static function memberEligibleForRole(int $memberId,int $societyId,int $roleId): bool
    {
        return Role::find($roleId)!==null && self::memberBelongsToSociety($memberId,$societyId);
    }

    public static function flatRoleIsLinked(int $memberId,int $societyId,int $roleId,?int $excludingUserId=null): bool
    {
        $sql='SELECT COUNT(*) FROM user_roles ur JOIN members target ON target.id=:mid
              WHERE ur.society_id=:sid AND ur.role_id=:rid AND ur.flat_id=target.flat_id';
        $params=['mid'=>$memberId,'sid'=>$societyId,'rid'=>$roleId];
        if($excludingUserId!==null){$sql.=' AND ur.user_id!=:uid';$params['uid']=$excludingUserId;}
        $stmt=db()->prepare($sql);$stmt->execute($params);return (int)$stmt->fetchColumn()>0;
    }

    public static function availableMembersForRole(int $societyId,int $roleId,?int $excludingUserId=null): array
    {
        if($roleId<=0 || !Role::find($roleId)) return [];
        $sql='SELECT m.id,m.name,m.member_type,m.email,m.phone,f.flat_number,w.name AS wing_name
              FROM members m JOIN flats f ON f.id=m.flat_id JOIN floors fl ON fl.id=f.floor_id JOIN wings w ON w.id=fl.wing_id
              WHERE m.society_id=:sid AND m.status="active"
              AND NOT EXISTS(SELECT 1 FROM user_roles ur WHERE ur.society_id=:lsid AND ur.role_id=:rid AND ur.flat_id=m.flat_id';
        $params=['sid'=>$societyId,'lsid'=>$societyId,'rid'=>$roleId];
        if($excludingUserId!==null){$sql.=' AND ur.user_id!=:uid';$params['uid']=$excludingUserId;}
        $sql.=') ORDER BY w.name,f.flat_number,m.name';
        $stmt=db()->prepare($sql);$stmt->execute($params);return $stmt->fetchAll();
    }

    public static function resetPassword(int $id,string $newPassword): void
    {
        $stmt=db()->prepare('UPDATE users SET password_hash=:hash,must_change_password=1 WHERE id=:id');
        $stmt->execute(['hash'=>password_hash($newPassword,PASSWORD_BCRYPT),'id'=>$id]);
    }

    public static function changeOwnPassword(int $id,string $newPassword): void
    {
        $stmt=db()->prepare('UPDATE users SET password_hash=:hash,must_change_password=0 WHERE id=:id');
        $stmt->execute(['hash'=>password_hash($newPassword,PASSWORD_BCRYPT),'id'=>$id]);
    }

    public static function recordLogin(int $userId): void
    {
        $stmt=db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=:id');$stmt->execute(['id'=>$userId]);
    }

    public static function logLoginHistory(?int $userId,string $emailAttempted,string $status): void
    {
        $stmt=db()->prepare('INSERT INTO login_history(user_id,email_attempted,ip_address,user_agent,status)
            VALUES(:user_id,:email,:ip,:agent,:status)');
        $stmt->execute(['user_id'=>$userId,'email'=>$emailAttempted,'ip'=>$_SERVER['REMOTE_ADDR']??null,'agent'=>$_SERVER['HTTP_USER_AGENT']??null,'status'=>$status]);
    }
}
