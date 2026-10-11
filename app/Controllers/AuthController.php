<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Captcha;
use App\Helpers\Csrf;
use App\Models\Society;
use App\Models\User;
use App\Services\PasswordResetService;

final class AuthController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_WINDOW_SECONDS = 900;

    public function showLogin(): void
    {
        if (Auth::check()) { header('Location: /dashboard'); exit; }
        Captcha::refresh();
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            Captcha::refresh(); http_response_code(419); $error='Session expired. Please try again.';
            require __DIR__ . '/../Views/auth/login.php'; return;
        }
        if (!Captcha::verify($_POST['captcha_code'] ?? null)) {
            Captcha::refresh(); $error='Invalid or expired security code. Please enter the new CAPTCHA code.';
            require __DIR__ . '/../Views/auth/login.php'; return;
        }

        $email=trim((string)($_POST['email']??'')); $password=(string)($_POST['password']??'');
        $societyCode=strtoupper(trim((string)($_POST['society_code']??'')));
        if($email===''||$password===''){
            Captcha::refresh(); $error='Email and password are required.';
            require __DIR__ . '/../Views/auth/login.php'; return;
        }

        $society=$societyCode!==''?Society::findByCode($societyCode):Society::current();
        $societyId=(int)($society['id']??0);
        if($societyId<=0){
            Captcha::refresh(); $error='Invalid society code.';
            require __DIR__ . '/../Views/auth/login.php'; return;
        }
        if($this->isRateLimited($email,$societyId)){
            Captcha::refresh(); $error='Too many failed attempts. Try again later.';
            require __DIR__ . '/../Views/auth/login.php'; return;
        }

        $user=User::findByEmail($societyId,$email);
        if(!$user||$user['status']!=='active'||!password_verify($password,$user['password_hash'])){
            User::logLoginHistory($user['id']??null,$email,'failed');
            Captcha::refresh(); $error='Invalid credentials.';
            require __DIR__ . '/../Views/auth/login.php'; return;
        }

        $permissions=User::permissionsForRole((int)$user['role_id']);
        Auth::login($user,$permissions); User::recordLogin((int)$user['id']); User::logLoginHistory((int)$user['id'],$email,'success');
        header('Location: /dashboard'); exit;
    }

    public function showForgotPassword(): void
    {
        if (Auth::check()) { header('Location: /dashboard'); exit; }
        Captcha::refresh();
        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function requestPasswordReset(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null) || !Captcha::verify($_POST['captcha_code'] ?? null)) {
            Captcha::refresh(); $error='Invalid or expired security code. Please try again.';
            require __DIR__ . '/../Views/auth/forgot_password.php'; return;
        }

        $email=trim((string)($_POST['email']??''));
        $societyCode=strtoupper(trim((string)($_POST['society_code']??'')));
        $society=$societyCode!==''?Society::findByCode($societyCode):Society::current();
        $societyId=(int)($society['id']??0);

        if($societyId>0&&filter_var($email,FILTER_VALIDATE_EMAIL)){
            PasswordResetService::requestUserReset($societyId,$email);
        }

        $message='If an active account matches those details, a password reset link has been sent to the registered email address.';
        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function showResetPassword(): void
    {
        $token=trim((string)($_GET['token']??'')); $type='user';
        $valid=PasswordResetService::validateToken($token,$type);
        require __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function completePasswordReset(): void
    {
        if(!Csrf::verify($_POST['_csrf']??null)){
            http_response_code(419); $error='Session expired. Please try again.';
            $token=trim((string)($_POST['token']??'')); $type='user';
            require __DIR__ . '/../Views/auth/reset_password.php'; return;
        }

        $token=trim((string)($_POST['token']??'')); $type='user';
        $password=(string)($_POST['password']??''); $confirmation=(string)($_POST['password_confirmation']??'');
        if(strlen($password)<8||$password!==$confirmation){
            $error='Password must be at least 8 characters and both entries must match.';
            require __DIR__ . '/../Views/auth/reset_password.php'; return;
        }

        try{$success=PasswordResetService::completeReset($token,$type,$password);}
        catch(\Throwable $e){error_log('SocietyOS password reset failed: '.$e->getMessage());$success=false;}

        if(!$success){
            $error='This password reset link is invalid, expired, or already used.';
            require __DIR__ . '/../Views/auth/reset_password.php'; return;
        }

        $message='Your password has been reset successfully. You can now sign in with your new password.';
        require __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function logout(): void { Auth::logout(); header('Location: /login'); exit; }

    private function isRateLimited(string $email,int $societyId): bool
    {
        $stmt=db()->prepare('SELECT COUNT(*) FROM login_history lh JOIN users u ON u.id=lh.user_id WHERE lh.email_attempted=:email AND u.society_id = :society_id AND lh.status="failed" AND lh.created_at>(NOW()-INTERVAL :window SECOND)');
        $stmt->execute(['email'=>$email,'society_id'=>$societyId,'window'=>self::LOCKOUT_WINDOW_SECONDS]);
        return (int)$stmt->fetchColumn()>=self::MAX_LOGIN_ATTEMPTS;
    }
}
