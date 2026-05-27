<?php

namespace App\Helpers;

class HotspotHelper
{
    /**
     * Parse profile on-login script metadata (Standard format)
     * Format: :put (",mode,price,validity,selling_price,lock_user,")
     */
    public static function parseProfileMetadata($script)
    {
        if (empty($script)) {
            return [];
        }

        // Look for :put (",...,") pattern
        preg_match('/:put \("([^"]+)"\)/', $script, $matches);
        if (isset($matches[1])) {
            // Explode CSV: ,mode,price,validity,selling_price,lock_user,
            $data = explode(',', $matches[1]);

            $clean = function ($val) {
                return ($val === '0' || $val === '0d' || $val === '0h' || $val === '0m') ? '' : $val;
            };

            return [
                'expired_mode' => $data[1] ?? '',
                'price' => $clean($data[2] ?? ''),
                'validity' => self::formatValidity($clean($data[3] ?? '')),
                'selling_price' => $clean($data[4] ?? ''),
                'lock_user' => $data[6] ?? '',
            ];
        }

        return [];
    }

    /**
     * Format validity string (e.g., 3d2h5m -> 3d 2h 5m)
     */
    public static function formatValidity($val)
    {
        if (empty($val)) {
            return '';
        }
        // Insert space after letters
        $val = preg_replace('/([a-z]+)/i', '$1 ', $val);

        return trim($val);
    }

    /**
     * Format expired mode code to readable text
     */
    public static function formatExpiredMode($mode)
    {
        switch ($mode) {
            case 'rem': return 'Remove';
            case 'ntf': return 'Notice';
            case 'remc': return 'Remove & Record';
            case 'ntfc': return 'Notice & Record';
            default: return $mode;
        }
    }

    /**
     * Format bytes to human readable string (KB, MB, GB)
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        if (empty($bytes) || $bytes === '0') {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }

    /**
     * Get User Status Code
     * Returns: active, limited, locked, expired
     */
    public static function getUserStatus($user)
    {
        // 1. Check for specific comment keywords (Highest Priority - usually set by scripts)
        $comment = strtolower($user['comment'] ?? '');

        // "exp" explicitly means expired by script
        if (strpos($comment, 'exp') !== false) {
            return 'expired';
        }

        // 2. Check Data Limit (Quota)
        $limitBytes = isset($user['limit-bytes-total']) ? (int) $user['limit-bytes-total'] : 0;
        if ($limitBytes > 0) {
            $bytesIn = isset($user['bytes-in']) ? (int) $user['bytes-in'] : 0;
            $bytesOut = isset($user['bytes-out']) ? (int) $user['bytes-out'] : 0;
            if (($bytesIn + $bytesOut) >= $limitBytes) {
                return 'limited';
            }
        }

        // 3. Check Disabled state
        if (($user['disabled'] ?? 'false') === 'true') {
            return 'locked';
        }

        // 4. Default
        return 'active';
    }
}
