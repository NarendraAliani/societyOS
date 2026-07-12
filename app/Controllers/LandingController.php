<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Society;

final class LandingController
{
    public function index(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }

        $society = Society::current();
        require __DIR__ . '/../Views/landing/index.php';
    }
}
