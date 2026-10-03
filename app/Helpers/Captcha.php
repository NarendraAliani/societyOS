<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Session-backed CAPTCHA used on every authentication surface.
 *
 * The challenge is one-time, expires quickly, and is regenerated after every
 * verification attempt. No external CAPTCHA provider or client-side secret is used.
 */
final class Captcha
{
    private const LENGTH = 6;
    private const TTL_SECONDS = 300;
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function refresh(): void
    {
        $value = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < self::LENGTH; $i++) {
            $value .= self::ALPHABET[random_int(0, $max)];
        }

        if (empty($_SESSION['_captcha_secret'])) {
            $_SESSION['_captcha_secret'] = bin2hex(random_bytes(32));
        }

        $_SESSION['_captcha'] = [
            'value' => $value,
            'hash' => hash_hmac('sha256', $value, $_SESSION['_captcha_secret']),
            'expires_at' => time() + self::TTL_SECONDS,
        ];
    }

    public static function verify(?string $input): bool
    {
        $challenge = $_SESSION['_captcha'] ?? null;
        $secret = $_SESSION['_captcha_secret'] ?? null;

        // CAPTCHA challenges are one-time regardless of success/failure.
        unset($_SESSION['_captcha']);

        if (!is_array($challenge) || !is_string($secret) || !is_string($input)) {
            return false;
        }

        if (($challenge['expires_at'] ?? 0) < time()) {
            return false;
        }

        $normalized = strtoupper(trim($input));
        if ($normalized === '' || strlen($normalized) > self::LENGTH) {
            return false;
        }

        $expected = hash_hmac('sha256', $normalized, $secret);
        return isset($challenge['hash']) && hash_equals((string) $challenge['hash'], $expected);
    }

    public static function svg(): string
    {
        if (empty($_SESSION['_captcha']['value']) || (int) ($_SESSION['_captcha']['expires_at'] ?? 0) < time()) {
            self::refresh();
        }

        $value = (string) $_SESSION['_captcha']['value'];

        $svg = '<svg class="captcha-svg" viewBox="0 0 300 82" role="img" aria-label="CAPTCHA code image" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="300" height="82" rx="10" fill="currentColor" opacity=".055"/>';

        // Noise lines make simple OCR/segmentation less reliable without requiring GD.
        for ($i = 0; $i < 8; $i++) {
            $x1 = random_int(5, 295);
            $y1 = random_int(8, 74);
            $x2 = random_int(5, 295);
            $y2 = random_int(8, 74);
            $opacity = random_int(15, 35) / 100;
            $svg .= sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="currentColor" stroke-width="%d" opacity="%.2f"/>',
                $x1,
                $y1,
                $x2,
                $y2,
                random_int(1, 2),
                $opacity
            );
        }

        $spacing = 45;
        foreach (str_split($value) as $index => $char) {
            $x = 28 + ($index * $spacing);
            $y = random_int(52, 61);
            $rotation = random_int(-16, 16);
            $size = random_int(29, 34);
            $svg .= sprintf(
                '<text x="%d" y="%d" font-family="monospace" font-size="%d" font-weight="700" text-anchor="middle" transform="rotate(%d %d %d)" fill="currentColor">%s</text>',
                $x,
                $y,
                $size,
                $rotation,
                $x,
                $y,
                htmlspecialchars($char, ENT_QUOTES, 'UTF-8')
            );
        }

        $svg .= '<circle cx="' . random_int(20, 280) . '" cy="' . random_int(10, 72) . '" r="2" fill="currentColor" opacity=".25"/>';
        $svg .= '<circle cx="' . random_int(20, 280) . '" cy="' . random_int(10, 72) . '" r="2" fill="currentColor" opacity=".25"/>';
        $svg .= '</svg>';

        return $svg;
    }
}
