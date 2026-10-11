<?php
declare(strict_types=1);
namespace App\Helpers;
use App\Models\User;
final class Auth
{
    public static function check(): bool { return !empty($_SESSION['user_id']); }
    public static function id(): ?int { return $_SESSION['user_id'] ?? null; }
    public static function role(): ?string { return $_SESSION['role_name'] ?? null; }
    public static function societyId(): ?int { return isset($_SESSION['society_id']) ? (int) $_SESSION['society_id'] : null; }
    public static function roleId(): ?int { return isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null; }
    public static function memberId(): ?int { return isset($_SESSION['member_id']) ? (int)$_SESSION['member_id'] : null; }
    public static function isResident(): bool { return in_array(self::role(),['resident','tenant'],true); }
    public static function mustChangePassword(): bool { return !empty($_SESSION['must_change_password']); }
    public static function roles(): array { $id=self::id(); return $id ? User::rolesForUser($id) : []; }
    public static function setPermissions(array $permissions): void { $_SESSION['permissions']=$permissions; }
    public static function can(string $permissionKey): bool { return in_array($permissionKey,$_SESSION['permissions']??[],true); }
    public static function login(array $user,array $permissions): void
    {
        Session::regenerate(); $_SESSION['user_id']=$user['id']; $_SESSION['user_name']=$user['name']; $_SESSION['society_id']=$user['society_id'];
        $_SESSION['role_id']=(int)$user['role_id']; $_SESSION['role_name']=$user['role_name']; $_SESSION['role_assignment_id']=(int)($user['role_assignment_id']??0);
        $_SESSION['member_id']=!empty($user['member_id'])?(int)$user['member_id']:null; $_SESSION['must_change_password']=!empty($user['must_change_password']);
        self::setPermissions($permissions);
    }
    public static function switchRole(int $roleId): void
    {
        $userId=self::id(); if($userId===null) return;
        $assignment=User::assignmentForUser($userId,$roleId);
        if(!$assignment) throw new \InvalidArgumentException('That role is not assigned to your account.');
        $_SESSION['role_id']=(int)$assignment['role_id']; $_SESSION['role_name']=$assignment['role_name']; $_SESSION['role_assignment_id']=(int)$assignment['id'];
        $_SESSION['member_id']=!empty($assignment['member_id'])?(int)$assignment['member_id']:null;
        self::setPermissions(User::permissionsForRole((int)$assignment['role_id']));
    }
    public static function clearPasswordChangeRequirement(): void { unset($_SESSION['must_change_password']); }
    public static function refreshName(string $name): void { $_SESSION['user_name']=$name; }
    public static function logout(): void
    {
        $_SESSION=[]; if(ini_get('session.use_cookies')){ $params=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']); } session_destroy();
    }
}
