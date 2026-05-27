<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\HotspotHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class ReportController extends Controller
{
    public function index($session)
    {
        $data = $this->getSellingReportData($session);
        if (! $data) {
            header('Location: /');
            exit;
        }

        return $this->view('reports/selling', $data);
    }

    public function sellingExport($session, $type)
    {
        $data = $this->getSellingReportData($session);
        if (! $data) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No data found']);
            exit;
        }

        $report = $data['report'];
        $exportData = [];

        foreach ($report as $row) {
            $exportData[] = [
                'Date/Batch' => $row['date'],
                'Status' => $row['status'] ?? '-',
                'Qty (Stock)' => $row['count'],
                'Used' => $row['realized_count'],
                'Realized Income' => $row['realized_total'],
                'Total Stock' => $row['total'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($exportData);
        exit;
    }

    private function getSellingReportData($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);

        if (! $config) {
            return null;
        }

        $API = new RouterOSAPI;
        $users = [];

        $profilePriceMap = [];
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $users = $API->comm('/ip/hotspot/user/print');
            $profiles = $API->comm('/ip/hotspot/user/profile/print');
            $API->disconnect();

            // Build Price Map from Profile Scripts
            foreach ($profiles as $p) {
                $meta = HotspotHelper::parseProfileMetadata($p['on-login'] ?? '');
                if (! empty($meta['price'])) {
                    $profilePriceMap[$p['name']] = intval($meta['price']);
                }
            }
        }

        // Aggregate Data
        $report = [];
        $totalIncome = 0;
        $totalVouchers = 0;

        // Realized (Used) Metrics
        $totalRealizedIncome = 0;
        $totalUsedVouchers = 0;

        foreach ($users as $user) {
            // Smart Price Detection
            $price = $this->detectPrice($user, $profilePriceMap);
            if ($price <= 0) {
                continue;
            }

            // Inject price back to user array for downstream logic
            $user['price'] = $price;

            // Determine Date from Comment
            $date = 'Unknown Date';
            $comment = $user['comment'] ?? '';

            if (! empty($comment)) {
                $date = $comment;
            }

            if (! isset($report[$date])) {
                $report[$date] = [
                    'date' => $date,
                    'count' => 0,
                    'total' => 0,
                    'realized_total' => 0,
                    'realized_count' => 0,
                ];
            }

            $price = intval($user['price']);

            // Check if Used
            // Criteria: uptime != 0s OR bytes-out > 0 OR bytes-in > 0
            $isUsed = false;
            if ((isset($user['uptime']) && $user['uptime'] != '0s') ||
                (isset($user['bytes-out']) && $user['bytes-out'] > 0)) {
                $isUsed = true;
            }

            $report[$date]['count']++;
            $report[$date]['total'] += $price;
            $totalIncome += $price;
            $totalVouchers++;

            if ($isUsed) {
                $report[$date]['realized_count']++;
                $report[$date]['realized_total'] += $price;
                $totalRealizedIncome += $price;
                $totalUsedVouchers++;
            }
        }

        // Calculate Status for each batch
        foreach ($report as &$row) {
            if ($row['realized_count'] === 0) {
                $row['status'] = 'New';
            } elseif ($row['realized_count'] >= $row['count']) {
                $row['status'] = 'Sold Out';
            } else {
                $row['status'] = 'Selling';
            }
        }
        unset($row);

        // Sort by key (Date/Comment) desc
        krsort($report);

        return [
            'session' => $session,
            'report' => $report,
            'totalIncome' => $totalIncome,
            'totalVouchers' => $totalVouchers,
            'totalRealizedIncome' => $totalRealizedIncome,
            'totalUsedVouchers' => $totalUsedVouchers,
            'currency' => $config['currency'] ?? 'Rp',
        ];
    }

    public function resume($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);

        if (! $config) {
            header('Location: /');
            exit;
        }

        $API = new RouterOSAPI;
        $users = [];

        $profilePriceMap = [];
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $users = $API->comm('/ip/hotspot/user/print');
            $profiles = $API->comm('/ip/hotspot/user/profile/print');
            $API->disconnect();

            foreach ($profiles as $p) {
                $meta = HotspotHelper::parseProfileMetadata($p['on-login'] ?? '');
                if (! empty($meta['price'])) {
                    $profilePriceMap[$p['name']] = intval($meta['price']);
                }
            }
        }

        // Initialize Aggregates
        $daily = [];
        $monthly = [];
        $yearly = [];
        $totalIncome = 0;

        // Realized Metrics for Resume?
        // Usually Resume is just general financial overview.
        // We'll stick to Stock for now unless requested, as Resume mimics Mikhmon's logic closer.
        // Or we can just calculate standard revenue based on Stock if that's what user expects for "Resume",
        // OR we can add Realized. Let's keep Resume simple first, focus on Selling Report.

        foreach ($users as $user) {
            $price = $this->detectPrice($user, $profilePriceMap);
            if ($price <= 0) {
                continue;
            }

            $user['price'] = $price;

            // Try to parse Date from Comment
            // Supported formats:
            // - MM/DD/YYYY or MM.DD.YYYY (US)
            // - DD-MM-YYYY (EU/ID)
            // - YYYY-MM-DD (ISO)
            // Regex explanations:
            // 1. \b starts word boundary to avoid matching parts of batch IDs (e.g. 711-...)
            // 2. We look for 3 groups of digits separated by / . or -
            $comment = $user['comment'] ?? '';
            $dateObj = null;

            if (preg_match('/\b(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{2,4})\b/', $comment, $matches)) {
                // Heuristic: If 3rd part is year (4 digits or > 31), use it.
                // If 1st part > 12, it's likely Day (DD-MM-YYYY).
                // Mivo Generator format often: MM.DD.YY or DD.MM.YY

                $p1 = intval($matches[1]);
                $p2 = intval($matches[2]);
                $p3 = intval($matches[3]);

                $year = $p3;
                $month = $p1;
                $day = $p2;

                // Adjust 2-digit year
                if ($year < 100) {
                    $year += 2000;
                }

                // Guess format
                // If p1 > 12, it must be Day. (DD-MM-YYYY)
                if ($p1 > 12) {
                    $day = $p1;
                    $month = $p2;
                }

                // Validate date
                if (checkdate($month, $day, $year)) {
                    $dateObj = (new \DateTime)->setDate($year, $month, $day);
                }
            }
            // Check for ISO YYYY-MM-DD
            elseif (preg_match('/\b(\d{4})[\/.-](\d{1,2})[\/.-](\d{1,2})\b/', $comment, $matches)) {
                if (checkdate($matches[2], $matches[3], $matches[1])) {
                    $dateObj = (new \DateTime)->setDate($matches[1], $matches[2], $matches[3]);
                }
            }

            // Fallback: If no date found -> "Unknown Date" in resume?
            // Resume requires Month/Year keys. If we can't parse date, we can't add to daily/monthly.
            // We'll skip or add to "Unknown"?
            // Current logic skips if !$dateObj
            if (! $dateObj) {
                continue;
            }

            $price = intval($user['price']);
            $totalIncome += $price;

            // Formats
            $dayKey = $dateObj->format('Y-m-d');
            $monthKey = $dateObj->format('Y-m');
            $yearKey = $dateObj->format('Y');

            // Daily
            if (! isset($daily[$dayKey])) {
                $daily[$dayKey] = 0;
            }
            $daily[$dayKey] += $price;

            // Monthly
            if (! isset($monthly[$monthKey])) {
                $monthly[$monthKey] = 0;
            }
            $monthly[$monthKey] += $price;

            // Yearly
            if (! isset($yearly[$yearKey])) {
                $yearly[$yearKey] = 0;
            }
            $yearly[$yearKey] += $price;
        }

        // Sort Keys
        ksort($daily);
        ksort($monthly);
        ksort($yearly);

        return $this->view('reports/resume', [
            'session' => $session,
            'daily' => $daily,
            'monthly' => $monthly,
            'yearly' => $yearly,
            'totalIncome' => $totalIncome,
            'currency' => $config['currency'] ?? 'Rp',
        ]);
    }

    /**
     * Smart Price Detection Logic
     * Hierarchy:
     * 1. Comment Override (p:5000)
     * 2. Profile Script (Standard Profile)
     * 3. Profile Name Fallback (50K) -- REMOVED loose number matching to avoid garbage data
     */
    private function detectPrice($user, $profileMap)
    {
        $comment = $user['comment'] ?? '';

        // 1. Comment Override (p:5000 or price:5000)
        // Updated: Added \b to prevent matching "up-123" as "p-123"
        if (preg_match('/\b(?:p|price)[:-]\s*(\d+)/i', $comment, $matches)) {
            return intval($matches[1]);
        }

        // 2. Profile Script
        $profile = $user['profile'] ?? 'default';
        if (isset($profileMap[$profile])) {
            return $profileMap[$profile];
        }

        // 3. Fallback: Parse Profile Name (Strict "K" notation only)
        // Matches "5K", "5k" -> 5000
        if (preg_match('/(\d+)k\b/i', $profile, $m)) {
            return intval($m[1]) * 1000;
        }

        // DEPRECATED: Loose number matching caused garbage data (e.g. "up-311" -> 311)

        return 0;
    }
}
