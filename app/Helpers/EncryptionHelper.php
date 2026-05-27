<?php

namespace App\Helpers;

use App\Config\SiteConfig;

class EncryptionHelper
{
    public static function encrypt($text)
    {
        if (empty($text)) {
            return '';
        }

        $key = SiteConfig::getSecretKey();

        // Simple OpenSSL encryption
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);

        return base64_encode($encrypted.'::'.$iv);
    }

    public static function decrypt($text)
    {
        if (empty($text)) {
            return '';
        }

        $key = SiteConfig::getSecretKey();

        try {
            $decoded = base64_decode($text, true);
            if ($decoded === false) {
                return $text;
            } // Not valid base64

            $parts = explode('::', $decoded, 2);
            if (count($parts) !== 2) {
                return $text; // Not our encrypted format, likely legacy/plain
            }

            [$encrypted_data, $iv] = $parts;

            return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
        } catch (\Exception $e) {
            return $text; // Fallback
        }
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
