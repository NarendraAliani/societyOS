<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;

final class AdminController
{
    public function users(): void { $pageTitle='Users';$users=User::allForSociety(Society::currentId());$roles=Role::all();require __DIR__.'/../Views/admin/users.php'; }
    public function createUser(): void { $pageTitle='Add User';$roles=Role::all();require __DIR__.'/../Views/admin/create_user.php'; }

    public function storeUser(): void
    {
        $this->verifyCsrf(); $name=trim((string)($_POST['name']??''));$email=trim((string)($_POST['email']??''));$roleId=(int)($_POST['role_id']??0);$memberId=(int)($_POST['member_id']??0);$password=(string)($_POST['password']??'');
        if($name===''||$email===''||$roleId<=0){Flash::set('error','Name, email, and role are required.');header('Location:/admin/users/create');exit;}
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){Flash::set('error','A valid email is required.');header('Location:/admin/users/create');exit;}
        if(!Role::find($roleId)||$memberId<=0||!User::memberEligibleForRole($memberId,Society::currentId(),$roleId)){Flash::set('error','A valid active resident/member must be linked for this role.');header('Location:/admin/users/create?needs_resident=1');exit;}
        if(User::flatRoleIsLinked($memberId,Society::currentId(),$roleId)){Flash::set('error','That home is already linked to another user account for the selected role.');header('Location:/admin/users/create');exit;}
        $existing=User::findByEmail(Society::currentId(),$email);
        if($existing){
            if(User::roleAlreadyAssigned((int)$existing['id'],$roleId)){Flash::set('error',"The account \"{$email}\" already has the selected role.");header('Location:/admin/users/create');exit;}
            try{User::addRole((int)$existing['id'],Society::currentId(),$roleId,$memberId);}catch(\InvalidArgumentException $e){Flash::set('error',$e->getMessage());header('Location:/admin/users/create');exit;}
            ActivityLog::log('admin','add_user_role',"Added role id {$roleId} to existing user id {$existing['id']}");
            Flash::set('success',"Role added to existing account \"{$email}\". The existing password remains unchanged.");header('Location:/admin/users');exit;
        }
        if(strlen($password)<8){Flash::set('error','A password of at least 8 characters is required when creating a new account.');header('Location:/admin/users/create');exit;}
        try{ $userId=User::create(Society::currentId(),$name,$email,trim((string)($_POST['phone']??''))?:null,$roleId,$password,$memberId); }
        catch(\Throwable $e){Flash::set('error','Unable to create the user account. Please verify the email and selected role.');header('Location:/admin/users/create');exit;}
        ActivityLog::log('admin','create_user',"Created user \"{$name}\" ({$email})");Flash::set('success',"User \"{$name}\" created. They must change this password on first login.");header('Location:/admin/users');exit;
    }

    public function availableResidentCandidates(): void
    {
        $roleId=(int)($_GET['role_id']??0);$excludingUserId=isset($_GET['user_id'])?(int)$_GET['user_id']:null;
        if(!Role::find($roleId)){header('Content-Type: application/json; charset=utf-8');echo json_encode(['items'=>[]]);return;}
        header('Content-Type: application/json; charset=utf-8');echo json_encode(['items'=>User::availableMembersForRole(Society::currentId(),$roleId,$excludingUserId)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function addUserRole(string $id): void
    {
        $this->verifyCsrf();$user=User::find((int)$id);$roleId=(int)($_POST['role_id']??0);$memberId=(int)($_POST['member_id']??0);
        if(!$user||(int)$user['society_id']!==Society::currentId()){Flash::set('error','User account not found.');header('Location:/admin/users');exit;}
        if(!Role::find($roleId)||$memberId<=0||!User::memberEligibleForRole($memberId,Society::currentId(),$roleId)){Flash::set('error','Select a valid role and active resident/member.');header('Location:/admin/users');exit;}
        if(User::roleAlreadyAssigned((int)$id,$roleId)){Flash::set('error','That role is already assigned to this user.');header('Location:/admin/users');exit;}
        if(User::flatRoleIsLinked($memberId,Society::currentId(),$roleId)){Flash::set('error','That home is already linked to another user account for the selected role.');header('Location:/admin/users');exit;}
        try{User::addRole((int)$id,Society::currentId(),$roleId,$memberId);}catch(\InvalidArgumentException $e){Flash::set('error',$e->getMessage());header('Location:/admin/users');exit;}
        ActivityLog::log('admin','add_user_role',"Added role id {$roleId} to user id {$id}");Flash::set('success','Role added to user account.');header('Location:/admin/users');exit;
    }

    public function setDefaultRole(string $id,string $roleId): void
    {
        $this->verifyCsrf();$user=User::find((int)$id);
        if(!$user||(int)$user['society_id']!==Society::currentId()){Flash::set('error','User account not found.');header('Location:/admin/users');exit;}
        try{User::setDefaultRole((int)$id,(int)$roleId);}catch(\InvalidArgumentException $e){Flash::set('error',$e->getMessage());header('Location:/admin/users');exit;}
        Flash::set('success','Default role updated.');header('Location:/admin/users');exit;
    }

    public function removeUserRole(string $id,string $roleId): void
    {
        $this->verifyCsrf();
        if((int)$id===Auth::id()&&(int)$roleId===Auth::roleId()){Flash::set('error','You cannot remove the role currently used by your own session.');header('Location:/admin/users');exit;}
        try{User::removeRole((int)$id,(int)$roleId);}catch(\InvalidArgumentException $e){Flash::set('error',$e->getMessage());header('Location:/admin/users');exit;}
        ActivityLog::log('admin','remove_user_role',"Removed role id {$roleId} from user id {$id}");Flash::set('success','Role removed from user account.');header('Location:/admin/users');exit;
    }

    public function updateUserStatus(string $id): void
    {
        $this->verifyCsrf();$status=in_array($_POST['status']??'', ['active','inactive','locked'],true)?$_POST['status']:'active';
        if((int)$id===Auth::id()&&$status!=='active'){Flash::set('error',"You can't deactivate your own account.");header('Location:/admin/users');exit;}
        User::updateStatus((int)$id,$status);ActivityLog::log('admin','update_user_status',"Updated user id {$id} status to \"{$status}\"");Flash::set('success','User status updated.');header('Location:/admin/users');exit;
    }

    public function resetPassword(string $id): void
    {
        $this->verifyCsrf();$password=(string)($_POST['password']??'');
        if(strlen($password)<8){Flash::set('error','New password must be at least 8 characters.');header('Location:/admin/users');exit;}
        User::resetPassword((int)$id,$password);ActivityLog::log('admin','reset_password',"Reset password for user id {$id}");Flash::set('success','Password reset. The user must change it on next login.');header('Location:/admin/users');exit;
    }

    public function switchRole(): void
    {
        $this->verifyCsrf();$roleId=(int)($_POST['role_id']??0);
        try{Auth::switchRole($roleId);}catch(\InvalidArgumentException $e){Flash::set('error',$e->getMessage());}
        header('Location:/dashboard');exit;
    }

    public function roles(): void { $pageTitle='Roles & Permissions';$roles=Role::all();$permissions=Role::allPermissions();require __DIR__.'/../Views/admin/roles.php'; }
    public function editRole(string $id): void { $pageTitle='Edit Role Permissions';$role=Role::find((int)$id);if(!$role){http_response_code(404);require __DIR__.'/../Views/errors/404.php';return;}$permissions=Role::allPermissions();$grantedIds=Role::permissionIdsForRole((int)$id);require __DIR__.'/../Views/admin/edit_role.php'; }
    public function updateRolePermissions(string $id): void { $this->verifyCsrf();$role=Role::find((int)$id);if($role&&$role['name']==='super_admin'){Flash::set('error','super_admin always has all permissions and cannot be edited.');header('Location:/admin/roles');exit;}$permissionIds=array_map('intval',$_POST['permission_ids']??[]);Role::setPermissions((int)$id,$permissionIds);ActivityLog::log('admin','update_role_permissions',"Updated permissions for role id {$id}");Flash::set('success','Role permissions updated.');header('Location:/admin/roles');exit; }
    public function activityLogs(): void { $pageTitle='Activity Logs';$logs=ActivityLog::recent(Society::currentId());$loginHistory=ActivityLog::loginHistory();require __DIR__.'/../Views/admin/activity_logs.php'; }
    private function verifyCsrf(): void { if(!Csrf::verify($_POST['_csrf']??null)){http_response_code(419);exit('Session expired. Go back and try again.');} }
}
