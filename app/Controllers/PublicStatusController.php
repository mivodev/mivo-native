<?php

namespace App\Controllers;

use App\Config\SiteConfig;
use App\Core\Controller;
use App\Helpers\FormatHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class PublicStatusController extends Controller
{
    // View: Show Search Page
    public function index($session)
    {
        // Just verify session existence to display Hotspot Name
        // Session verified by RouterCheckMiddleware
        $configModel = new Config;
        $creds = $configModel->getSession($session);

        $data = [
            'session' => $session,
            'hotspot_name' => $creds['hotspot_name'] ?? 'Hotspot',
            'footer_text' => SiteConfig::getFooter(),
        ];

        return $this->view('public/status', $data);
    }

    // API: Check Status
    public function check($codeUrl = null)
    {
        header('Content-Type: application/json');

        // Allow POST and GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);

            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // Session: Try Body -> Try Header
        $session = $input['session'] ?? '';
        if (empty($session)) {
            $headers = getallheaders();
            // Handle case-insensitivity of headers
            $session = $headers['X-Mivo-Session'] ?? ($headers['x-mivo-session'] ?? '');
        }

        // Code: Can be in URL or Body
        $code = $codeUrl ?? ($input['code'] ?? '');

        if (empty($session) || empty($code)) {
            http_response_code(400);
            echo json_encode(['error' => 'Session and Voucher Code are required']);

            return;
        }

        $configModel = new Config;
        $creds = $configModel->getSession($session);

        if (! $creds) {
            http_response_code(404);
            echo json_encode(['error' => 'Session not found']);

            return;
        }

        $password = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password = RouterOSAPI::decrypt($password);
        }

        $api = new RouterOSAPI;
        if (! $api->connect($creds['ip'], $creds['user'], $password)) {
            http_response_code(500);
            echo json_encode(['error' => 'Router Connection Failed']);

            return;
        }

        // Logic Refactor: Pivot to User Table as primary source for Voucher Details
        // 1. Check User in Database
        $user = $api->comm('/ip/hotspot/user/print', [
            '?name' => $code,
        ]);

        if (! empty($user)) {
            $u = $user[0];

            // --- SECURITY CHECK: Hide Unused Vouchers (UNLESS ACTIVE) ---
            $uptimeRaw = $u['uptime'] ?? '0s';
            $bytesIn = intval($u['bytes-in'] ?? 0);
            $bytesOut = intval($u['bytes-out'] ?? 0);

            // Check if active first
            $active = $api->comm('/ip/hotspot/active/print', [
                '?user' => $code,
            ]);
            $isActive = ! empty($active);

            // If Empty Stats AND Not Active => Hide (It's an unused new voucher)
            // If Empty Stats BUT Active => Show! (It's a fresh session)
            if (! $isActive && ($uptimeRaw === '0s' || empty($uptimeRaw)) && ($bytesIn + $bytesOut) === 0) {
                $api->disconnect();
                echo json_encode(['success' => false, 'message' => 'Voucher Not Found']);

                return;
            }

            // --- SECURITY CHECK: Hide Unlimited Members (UNLESS ACTIVE) ---
            $limitBytes = isset($u['limit-bytes-total']) ? intval($u['limit-bytes-total']) : 0;
            $limitUptime = $u['limit-uptime'] ?? '0s';

            if (! $isActive && $limitBytes === 0 && ($limitUptime === '0s' || empty($limitUptime))) {
                // Hide unlimited members if they are offline to prevent enumeration
                $api->disconnect();
                echo json_encode(['success' => false, 'message' => 'Voucher Not Found']);

                return;
            }

            // --- CALCULATIONS ---
            $dataUsed = $bytesIn + $bytesOut;
            $dataLeft = 'Unlimited';

            if ($limitBytes > 0) {
                $remaining = max(0, $limitBytes - $dataUsed);
                $dataLeft = ($remaining === 0) ? '0 B' : FormatHelper::formatBytes($remaining);
            }

            // Validity Logic
            $validityRaw = $u['limit-uptime'] ?? '0s';
            $validityDisplay = ($validityRaw === '0s') ? 'Unlimited' : FormatHelper::elapsedTime($validityRaw);
            $expiration = '-';

            $comment = strtolower($u['comment'] ?? '');
            if (preg_match('/exp\W+([a-z]{3}\/\d{2}\/\d{4}\s\d{2}:\d{2}:\d{2})/', $comment, $matches)) {
                $expiration = $matches[1];
            } elseif ($validityRaw !== '0s') {
                $totalSeconds = FormatHelper::parseDuration($validityRaw);
                $usedSeconds = FormatHelper::parseDuration($uptimeRaw);
                $remainingSeconds = max(0, $totalSeconds - $usedSeconds);

                if ($remainingSeconds > 0) {
                    $expiration = date('d M Y H:i', time() + $remainingSeconds);
                } else {
                    $expiration = 'Expired';
                }
            }

            // BASE STATUS
            $status = 'offline';
            $statusLabel = 'Valid / Offline';
            $isDisabled = ($u['disabled'] ?? 'false') === 'true';

            // Calculate Time Left
            $timeLeft = 'Unlimited';
            if ($expiration !== '-' && $expiration !== 'Expired') {
                $expTime = strtotime($expiration);
                if ($expTime) {
                    $rem = max(0, $expTime - time());
                    $timeLeft = ($rem === 0) ? 'Expired' : FormatHelper::formatSeconds($rem);
                }
            } elseif ($validityRaw !== '0s') {
                $totalSeconds = FormatHelper::parseDuration($validityRaw);
                $usedSeconds = FormatHelper::parseDuration($uptimeRaw);
                $rem = max(0, $totalSeconds - $usedSeconds);
                $timeLeft = ($rem === 0) ? 'Expired' : FormatHelper::formatSeconds($rem);
            }

            if (strpos($comment, 'exp') !== false || ($expiration === 'Expired')) {
                $status = 'expired';
                $statusLabel = 'Expired';
            } elseif ($limitBytes > 0 && $dataUsed >= $limitBytes) {
                $status = 'limited';
                $statusLabel = 'Quota Exceeded';
            } elseif ($isDisabled) {
                $status = 'locked';
                $statusLabel = 'Locked / Disabled';
            }

            // 2. CHECK ACTIVE OVERRIDE
            // If user is conceptually valid (or even if limited?), check if they are currently active
            // Because they might be active BUT expiring soon, or active BUT over quota (if server hasn't kicked them yet)
            // $active already fetched above in Security Check

            if ($isActive) {
                $status = 'active';
                $statusLabel = 'Active (Online)';
            }

            $data = [
                'status' => $status,
                'status_label' => $statusLabel,
                'username' => $u['name'] ?? 'Unknown',
                'profile' => $u['profile'] ?? 'default',
                'uptime_used' => FormatHelper::elapsedTime($uptimeRaw),
                'validity' => $validityDisplay,
                'data_used' => FormatHelper::formatBytes($dataUsed),
                'data_left' => $dataLeft,
                'expiration' => $expiration,
                'time_left' => $timeLeft,
                'comment' => $u['comment'] ?? '',
            ];

            echo json_encode(['success' => true, 'data' => $data]);
            $api->disconnect();

            return;
        }

        // 3. Fallback: Check Active Only (Trial Users or IP Bindings not in User Table)
        $active = $api->comm('/ip/hotspot/active/print', [
            '?user' => $code,
        ]);

        if (! empty($active)) {
            $u = $active[0];
            $data = [
                'status' => 'active',
                'status_label' => 'Active (Online)',
                'username' => $u['user'] ?? 'Unknown',
                'profile' => '-', // Active usually doesn't have profile name directly unless queried
                'uptime_used' => FormatHelper::elapsedTime($u['uptime'] ?? '0s'),
                'validity' => '-',
                'data_used' => FormatHelper::formatBytes(intval($u['bytes-in'] ?? 0) + intval($u['bytes-out'] ?? 0)),
                'data_left' => 'Unknown',
                'time_left' => isset($u['session-time-left']) ? FormatHelper::elapsedTime($u['session-time-left']) : '-',
                'expiration' => '-',
                'comment' => '',
            ];
            echo json_encode(['success' => true, 'data' => $data]);
            $api->disconnect();

            return;
        }

        $api->disconnect();
        echo json_encode(['success' => false, 'message' => 'Voucher Not Found']);
    }
}
