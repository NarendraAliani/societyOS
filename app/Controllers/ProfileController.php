<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\Society;
use App\Models\User;

final class ProfileController
{
    public function show(): void
    {
        $pageTitle = 'My Profile';
        $user = User::find((int) Auth::id());
        require __DIR__ . '/../Views/profile/show.php';
    }

    public function edit(): void
    {
        $pageTitle = 'Edit Profile';
        $user = User::find((int) Auth::id());
        require __DIR__ . '/../Views/profile/edit.php';
    }

    public function update(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }

        $userId = (int) Auth::id();
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'A valid name and email are required.');
            header('Location: /profile/edit');
            exit;
        }

        if (User::emailExists(Society::currentId(), $email, $userId)) {
            Flash::set('error', "Email \"{$email}\" is already in use by another user.");
            header('Location: /profile/edit');
            exit;
        }

        User::updateOwnProfile($userId, $name, $email, $phone ?: null);
        Auth::refreshName($name);

        Flash::set('success', 'Profile updated.');
        header('Location: /profile');
        exit;
    }

    public function updatePassword(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }

        $userId = (int) Auth::id();
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $user = User::find($userId);

        if (!$user || !password_verify($current, $user['password_hash'])) {
            Flash::set('error', 'Current password is incorrect.');
            header('Location: /profile/password');
            exit;
        }

        if (strlen($new) < 8) {
            Flash::set('error', 'New password must be at least 8 characters.');
            header('Location: /profile/password');
            exit;
        }

        if ($new !== $confirm) {
            Flash::set('error', 'New password and confirmation do not match.');
            header('Location: /profile/password');
            exit;
        }

        User::changeOwnPassword($userId, $new);

        Flash::set('success', 'Password changed.');
        header('Location: /profile');
        exit;
    }

    public function showChangePassword(): void
    {
        $pageTitle = 'Change Password';
        require __DIR__ . '/../Views/profile/password.php';
    }
}
