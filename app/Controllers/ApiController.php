<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class ApiController extends Controller
{
    public function getInterfaces()
    {
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);

            return;
        }

        // Get JSON Input
        $input = json_decode(file_get_contents('php://input'), true);

        $ip = $input['ip'] ?? '';
        $user = $input['user'] ?? '';
        $pass = $input['password'] ?? '';
        $id = $input['id'] ?? null;
        $port = $input['port'] ?? 8728; // Default port

        // Fallback to stored password if empty and ID provided (Edit Mode)
        if (empty($pass) && ! empty($id)) {
            $configModel = new Config;
            $session = $configModel->getSessionById($id);
            if ($session && ! empty($session['password'])) {
                // Config::getSessionById already decrypts the password
                $pass = $session['password'];
            }
        }

        if (empty($ip) || empty($user)) {
            http_response_code(400);
            echo json_encode(['error' => 'IP Address and Username are required']);

            return;
        }

        $api = new RouterOSAPI;
        // $api->debug = true; // Enable for debugging
        $api->port = (int) $port;

        if ($api->connect($ip, $user, $pass)) {
            $api->write('/interface/print');
            $read = $api->read(false);
            $interfaces = $api->parseResponse($read);
            $api->disconnect();

            $list = [];
            foreach ($interfaces as $iface) {
                if (isset($iface['name'])) {
                    $list[] = $iface['name'];
                }
            }

            // Return success
            echo json_encode([
                'success' => true,
                'interfaces' => $list,
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'error' => 'Connection failed. Check IP, User, Password, or connectivity.',
            ]);
        }
    }
}
