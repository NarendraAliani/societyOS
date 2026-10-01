<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Captcha;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Helpers\PlatformAuth;
use App\Models\PlatformAdmin;
use App\Services\SocietyProvisioner;

final class PlatformController
{
    public function showLogin(): void
    {
        if (PlatformAuth::check()) {
            header('Location: /platform/societies');
            exit;
        }

        Captcha::refresh();
        require __DIR__.'/../Views/platform/login.php';
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf']??null)) {
            Captcha::refresh();
            http_response_code(419);
            $error='Session expired. Please try again.';
            require __DIR__.'/../Views/platform/login.php';
            return;
        }

        if (!Captcha::verify($_POST['captcha_code'] ?? null)) {
            Captcha::refresh();
            $error='Invalid or expired security code. Please enter the new CAPTCHA code.';
            require __DIR__.'/../Views/platform/login.php';
            return;
        }

        $email=strtolower(trim((string)($_POST['email']??''))); $password=(string)($_POST['password']??'');
        if($email===''||$password===''){
            Captcha::refresh();
            $error='Email and password are required.';
            require __DIR__.'/../Views/platform/login.php';
            return;
        }

        if(PlatformAdmin::recentLoginAttempts($email)>=5){
            Captcha::refresh();
            $error='Too many failed attempts. Try again later.';
            require __DIR__.'/../Views/platform/login.php';
            return;
        }

        $admin=PlatformAdmin::findByEmail($email);
        if(!$admin||$admin['status']!=='active'||!password_verify($password,$admin['password_hash'])){
            PlatformAdmin::logLoginHistory($admin['id']??null,$email,'failed');
            Captcha::refresh();
            $error='Invalid platform administrator credentials.';
            require __DIR__.'/../Views/platform/login.php';
            return;
        }

        PlatformAuth::login($admin);
        PlatformAdmin::recordLogin((int)$admin['id']);
        PlatformAdmin::logLoginHistory((int)$admin['id'],$email,'success');
        header('Location: /platform/societies');
        exit;
    }

    public function societies(): void
    {
        $pageTitle='Platform — Societies';
        $societies=db()->query('SELECT s.id,s.code,s.name,s.city,s.state,s.created_at,(SELECT COUNT(*) FROM users u WHERE u.society_id=s.id) AS user_count,(SELECT COUNT(*) FROM wings w WHERE w.society_id=s.id) AS wing_count FROM society s ORDER BY s.id')->fetchAll();
        require __DIR__.'/../Views/platform/societies.php';
    }

    public function createSociety(): void { $pageTitle='Create Society'; require __DIR__.'/../Views/platform/create_society.php'; }

    public function storeSociety(): void
    {
        if(!Csrf::verify($_POST['_csrf']??null)){http_response_code(419);exit('Session expired. Go back and try again.');}
        try{$result=SocietyProvisioner::provision($_POST);Flash::set('success',sprintf('Society %s created. Super Admin %s can sign in with the Society Code.',$result['code'],$_POST['admin_email']));}
        catch(\Throwable $e){Flash::set('error',$e->getMessage());}
        header('Location: /platform/societies');exit;
    }

    public function password(): void { $pageTitle='Platform Administrator Password'; require __DIR__.'/../Views/platform/password.php'; }

    public function updatePassword(): void
    {
        if(!Csrf::verify($_POST['_csrf']??null)){http_response_code(419);exit('Session expired. Go back and try again.');}
        $password=(string)($_POST['password']??'');$confirmation=(string)($_POST['password_confirmation']??'');
        if(strlen($password)<8||$password!==$confirmation){Flash::set('error','Password must be at least 8 characters and both entries must match.');header('Location:/platform/password');exit;}
        PlatformAdmin::updatePassword((int)PlatformAuth::id(),$password);PlatformAuth::clearPasswordChangeRequirement();Flash::set('success','Platform administrator password updated.');header('Location:/platform/societies');exit;
    }

    public function logout(): void { PlatformAuth::logout(); header('Location: /platform/login'); exit; }
}
