<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Settings;
use App\Models\Society;

final class SettingsController
{
    private const KEYS = [
        'theme_default' => ['light', 'dark', 'mid'],
        'font_size_default' => ['small', 'medium', 'large'],
    ];

    public function index(): void
    {
        $pageTitle = 'Settings';
        $societyId = Society::currentId();

        $settings = [
            'theme_default' => Settings::get($societyId, 'theme_default', 'light'),
            'font_size_default' => Settings::get($societyId, 'font_size_default', 'medium'),
            'penalty_interest_rate_percent' => Settings::get($societyId, 'penalty_interest_rate_percent', config()['penalty_interest_rate_percent']),
            'upload_max_size_mb' => Settings::get($societyId, 'upload_max_size_mb', config()['upload_max_size_mb']),
        ];

        require __DIR__ . '/../Views/admin/settings.php';
    }

    public function update(): void
    {
        $this->verifyCsrf();
        $societyId = Society::currentId();

        $themeDefault = $_POST['theme_default'] ?? '';
        $fontSizeDefault = $_POST['font_size_default'] ?? '';
        $interestRate = $_POST['penalty_interest_rate_percent'] ?? '';
        $uploadMaxSize = $_POST['upload_max_size_mb'] ?? '';

        if (!in_array($themeDefault, self::KEYS['theme_default'], true)
            || !in_array($fontSizeDefault, self::KEYS['font_size_default'], true)
            || !is_numeric($interestRate) || (float) $interestRate < 0
            || !is_numeric($uploadMaxSize) || (int) $uploadMaxSize < 1
        ) {
            Flash::set('error', 'All fields are required and must be valid — interest rate cannot be negative, upload size must be at least 1 MB.');
            header('Location: /admin/settings');
            exit;
        }

        Settings::set($societyId, 'theme_default', $themeDefault);
        Settings::set($societyId, 'font_size_default', $fontSizeDefault);
        Settings::set($societyId, 'penalty_interest_rate_percent', (string) (float) $interestRate);
        Settings::set($societyId, 'upload_max_size_mb', (string) (int) $uploadMaxSize);

        ActivityLog::log('settings', 'update', 'Updated system settings');
        Flash::set('success', 'Settings updated.');
        header('Location: /admin/settings');
        exit;
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
