<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Convert MikroTik duration string to human readable format.
     * Example: "3w1d8h56m19s" -> "3 Weeks 1 Day 8 Hours 56 Minutes 19 Seconds"
     *
     * @param  string  $string
     * @return string
     */
    public static function elapsedTime($string)
    {
        if (empty($string)) {
            return '-';
        }

        // Mikrotik formats:
        // 1. "3w1d8h56m19s" (Full)
        // 2. "00:05:00" (Simple H:i:s)
        // 3. "1d 05:00:00" (Hybrid)
        // 4. "sep/02/2023 10:00:00" (Absolute date, rarely used for uptime but useful to catch)

        // Maps Mikrotik abbreviations to Human terms (Plural handled in logic)
        $maps = [
            'w' => 'Week',
            'd' => 'Day',
            'h' => 'Hour',
            'm' => 'Minute',
            's' => 'Second',
        ];

        // Result container
        $parts = [];

        // Check for simple colon format (H:i:s)
        if (strpos($string, ':') !== false && strpos($string, 'w') === false && strpos($string, 'd') === false) {
            return $string; // Return as is or parse H:i:s if needed
        }

        // Parse regex for w, d, h, m, s
        // preg_match_all('/(\d+)([wdhms])/', $string, $matches, PREG_SET_ORDER);

        // Manual parsing to handle mixed cases more robustly or just regex
        foreach ($maps as $key => $label) {
            if (preg_match('/(\d+)'.$key.'/', $string, $match)) {
                $value = intval($match[1]);
                if ($value > 0) {
                    $parts[] = $value.' '.$label.($value > 1 ? 's' : '');
                }
            }
        }

        // If no matches found, straightforward return (maybe it's raw seconds or weird format)
        if (empty($parts)) {
            if ($string === '0s' || $string === '00:00:00') {
                return '-';
            }

            return $string;
        }

        return implode(' ', $parts);
    }

    /**
     * Capitalize each word (Title Case)
     *
     * @param  string  $string
     * @return string
     */
    public static function capitalize($string)
    {
        return ucwords(strtolower($string));
    }

    /**
     * Format Currency
     *
     * @param  int|float  $number
     * @param  string  $prefix
     * @return string
     */
    public static function formatCurrency($number, $prefix = '')
    {
        return $prefix.' '.number_format($number, 0, ',', '.');
    }

    /**
     * Format Bytes to KB, MB, GB
     *
     * @param  int  $bytes
     * @param  int  $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) {
            return '-';
        }

        $base = log($bytes, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision).' '.$suffixes[floor($base)];
    }

    /**
     * Format Date
     *
     * @param  string  $dateStr
     * @param  string  $format
     * @return string
     */
    public static function formatDate($dateStr, $format = 'd M Y H:i')
    {
        if (empty($dateStr)) {
            return '-';
        }
        // Handle Mikrotik default date formats if needed, usually they are readable
        // e.g. "jan/02/1970 00:00:00"
        $time = strtotime($dateStr);
        if (! $time) {
            return $dateStr;
        }

        return date($format, $time);
    }

    /**
     * Convert Seconds to Human Readable format
     *
     * @param  int  $seconds
     * @return string
     */
    public static function formatSeconds($seconds)
    {
        if ($seconds <= 0) {
            return '0s';
        }

        $w = floor($seconds / 604800);
        $d = floor(($seconds % 604800) / 86400);
        $h = floor(($seconds % 86400) / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        $parts = [];
        if ($w > 0) {
            $parts[] = $w.'w';
        }
        if ($d > 0) {
            $parts[] = $d.'d';
        }
        if ($h > 0) {
            $parts[] = $h.'h';
        }
        if ($m > 0) {
            $parts[] = $m.'m';
        }
        if ($s > 0 || empty($parts)) {
            $parts[] = $s.'s';
        }

        return implode('', $parts);
    }

    /**
     * Parse MikroTik duration string to Seconds (int)

     * Supports: 1d2h3m, 00:00:00, 1d 00:00:00
     */
    public static function parseDuration($string)
    {
        if (empty($string)) {
            return 0;
        }

        $string = trim($string);
        $totalSeconds = 0;

        // 1. Handle "00:00:00" or "1d 00:00:00" (Colons)
        if (strpos($string, ':') !== false) {
            $parts = explode(' ', $string);
            $timePart = end($parts); // 00:00:00

            // Calc time part
            $t = explode(':', $timePart);
            if (count($t) === 3) {
                $totalSeconds += ($t[0] * 3600) + ($t[1] * 60) + $t[2];
            } elseif (count($t) === 2) { // 00:00 (mm:ss or hh:mm? usually hh:mm in routeros logs, but 00:00:59 is uptime)
                // Assumption: if 2 parts, treat as MM:SS if small, or HH:MM?
                // RouterOS uptime is usually HH:MM:SS. Let's assume standard time ref.
                // Actually RouterOS uptime often drops hours if 0.
                // SAFE BET: Just Parse standard 3 parts.
                $totalSeconds += ($t[0] * 60) + $t[1];
            }

            // Calc Day part "1d"
            if (count($parts) > 1) {
                $dayPart = $parts[0]; // 1d
                $totalSeconds += intval($dayPart) * 86400;
            }

            return $totalSeconds;
        }

        // 2. Handle "1w2d3h4m5s" (Letters)
        if (preg_match_all('/(\d+)([wdhms])/', $string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $val = intval($m[1]);
                $unit = $m[2];
                switch ($unit) {
                    case 'w': $totalSeconds += $val * 604800;
                        break;
                    case 'd': $totalSeconds += $val * 86400;
                        break;
                    case 'h': $totalSeconds += $val * 3600;
                        break;
                    case 'm': $totalSeconds += $val * 60;
                        break;
                    case 's': $totalSeconds += $val;
                        break;
                }
            }

            return $totalSeconds;
        }

        // 3. Raw number?
        return intval($string);
    }
}
