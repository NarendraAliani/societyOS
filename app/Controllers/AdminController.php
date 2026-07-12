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
    public function users(): void
    {
        $pageTitle = 'Users';
        $users = User::allForSociety(Society::currentId());
        $roles = Role::all();
        require __DIR__ . '/../Views/admin/users.php';
    }

    public function createUser(): void
    {
        $pageTitle = 'Add User';
        $roles = Role::all();
        require __DIR__ . '/../Views/admin/create_user.php';
    }

    public function storeUser(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $roleId <= 0 || strlen($password) < 8) {
            Flash::set('error', 'Name, email, role, and a password of at least 8 characters are required.');
            header('Location: /admin/users/create');
            exit;
        }

        if (User::emailExists(Society::currentId(), $email)) {
            Flash::set('error', "A user with email \"{$email}\" already exists.");
            header('Location: /admin/users/create');
            exit;
        }

        User::create(Society::currentId(), $name, $email, trim((string) ($_POST['phone'] ?? '')) ?: null, $roleId, $password);

        Flash::set('success', "User \"{$name}\" created. They must change their password on first login.");
        header('Location: /admin/users');
        exit;
    }

    public function updateUser(string $id): void
    {
        $this->verifyCsrf();

        $roleId = (int) ($_POST['role_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'locked'], true) ? $_POST['status'] : 'active';

        if ($roleId <= 0) {
            Flash::set('error', 'A role is required.');
            header('Location: /admin/users');
            exit;
        }

        if ((int) $id === Auth::id() && $status !== 'active') {
            Flash::set('error', "You can't deactivate your own account.");
            header('Location: /admin/users');
            exit;
        }

        User::updateRoleAndStatus((int) $id, $roleId, $status);
        Flash::set('success', 'User updated.');
        header('Location: /admin/users');
        exit;
    }

    public function resetPassword(string $id): void
    {
        $this->verifyCsrf();

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 8) {
            Flash::set('error', 'New password must be at least 8 characters.');
            header('Location: /admin/users');
            exit;
        }

        User::resetPassword((int) $id, $password);
        Flash::set('success', 'Password reset. The user must change it on next login.');
        header('Location: /admin/users');
        exit;
    }

    public function roles(): void
    {
        $pageTitle = 'Roles & Permissions';
        $roles = Role::all();
        $permissions = Role::allPermissions();
        require __DIR__ . '/../Views/admin/roles.php';
    }

    public function editRole(string $id): void
    {
        $pageTitle = 'Edit Role Permissions';
        $role = Role::find((int) $id);
        if (!$role) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $permissions = Role::allPermissions();
        $grantedIds = Role::permissionIdsForRole((int) $id);
        require __DIR__ . '/../Views/admin/edit_role.php';
    }

    public function updateRolePermissions(string $id): void
    {
        $this->verifyCsrf();

        $role = Role::find((int) $id);
        if ($role && $role['name'] === 'super_admin') {
            Flash::set('error', 'super_admin always has all permissions and cannot be edited.');
            header('Location: /admin/roles');
            exit;
        }

        $permissionIds = array_map('intval', $_POST['permission_ids'] ?? []);
        Role::setPermissions((int) $id, $permissionIds);

        Flash::set('success', 'Role permissions updated.');
        header('Location: /admin/roles');
        exit;
    }

    public function activityLogs(): void
    {
        $pageTitle = 'Activity Logs';
        $logs = ActivityLog::recent(Society::currentId());
        $loginHistory = ActivityLog::loginHistory();
        require __DIR__ . '/../Views/admin/activity_logs.php';
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
