<?php
declare(strict_types=1);
namespace App\Helpers;

final class SecretCipher
{
    private const PREFIX = 'enc:v1:';

    public static function encrypt(string $plain): string
    {
        if ($plain === '') return '';
        $key = hash('sha256', (string) config()['key'], true);
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) throw new \RuntimeException('Unable to encrypt integration secret.');
        return self::PREFIX . base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(?string $value): string
    {
        if ($value === null || $value === '') return '';
        if (!str_starts_with($value, self::PREFIX)) return $value;
        $raw = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if ($raw === false || strlen($raw) < 28) return '';
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $key = hash('sha256', (string) config()['key'], true);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return $plain === false ? '' : $plain;
    }

    public static function masked(?string $value): string
    {
        $value = self::decrypt($value);
        if ($value === '') return '';
        if (strlen($value) <= 4) return str_repeat('•', strlen($value));
        return substr($value, 0, 2) . str_repeat('•', max(2, strlen($value) - 4)) . substr($value, -2);
    }
}
