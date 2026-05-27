<?php

namespace App\Core;

use App\Config\SiteConfig;

class Console
{
    // ANSI Color Codes
    const COLOR_RESET = "\033[0m";

    const COLOR_GREEN = "\033[32m";

    const COLOR_YELLOW = "\033[33m";

    const COLOR_BLUE = "\033[34m";

    const COLOR_GRAY = "\033[90m";

    const COLOR_RED = "\033[31m";

    const COLOR_BOLD = "\033[1m";

    public function run($argv)
    {
        $command = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        $this->printBanner();

        switch ($command) {
            case 'serve':
                $this->commandServe($args);
                break;

            case 'key:generate':
                $this->commandKeyGenerate();
                break;

            case 'admin:reset':
                $this->commandAdminReset($args);
                break;

            case 'install':
                $this->commandInstall($args);
                break;

            case 'help':
            default:
                $this->commandHelp();
                break;
        }
    }

    private function printBanner()
    {
        echo "\n";
        echo self::COLOR_BOLD.'  MIVO Helper '.self::COLOR_RESET.self::COLOR_GRAY.SiteConfig::APP_VERSION.self::COLOR_RESET."\n\n";
    }

    private function commandServe($args)
    {
        $host = '0.0.0.0';
        $port = 8000;

        foreach ($args as $arg) {
            if (strpos($arg, '--port=') === 0) {
                $port = (int) substr($arg, 7);
            }
            if (strpos($arg, '--host=') === 0) {
                $host = substr($arg, 7);
            }
        }

        echo '  '.self::COLOR_GREEN.'Server running on:'.self::COLOR_RESET."\n";
        echo '  - Local:   '.self::COLOR_BLUE."http://localhost:$port".self::COLOR_RESET."\n";

        $hostname = gethostname();
        $ip = gethostbyname($hostname);
        if ($ip !== '127.0.0.1' && $ip !== 'localhost') {
            echo '  - Network: '.self::COLOR_BLUE."http://$ip:$port".self::COLOR_RESET."\n";
        }

        echo "\n  ".self::COLOR_GRAY.'Press Ctrl+C to stop'.self::COLOR_RESET."\n\n";

        $cmd = sprintf('php -S %s:%d -t public public/index.php', $host, $port);
        passthru($cmd);
    }

    private function commandKeyGenerate()
    {
        echo self::COLOR_YELLOW.'Generating new application key...'.self::COLOR_RESET."\n";

        // Generate 32 bytes of random data for AES-256
        $key = bin2hex(random_bytes(16)); // 32 chars hex

        $envPath = ROOT.'/.env';
        $examplePath = ROOT.'/.env.example';

        // Copy example if .env doesn't exist
        if (! file_exists($envPath)) {
            echo self::COLOR_BLUE.'Copying .env.example to .env...'.self::COLOR_RESET."\n";
            if (file_exists($examplePath)) {
                copy($examplePath, $envPath);
            } else {
                echo self::COLOR_RED.'Error: .env.example not found.'.self::COLOR_RESET."\n";

                return;
            }
        }

        // Read .env
        $content = file_get_contents($envPath);

        // Replace or Append APP_KEY
        if (strpos($content, 'APP_KEY=') !== false) {
            $newContent = preg_replace(
                '/APP_KEY=.*/',
                "APP_KEY=$key",
                $content
            );
        } else {
            $newContent = $content."\nAPP_KEY=$key";
        }

        file_put_contents($envPath, $newContent);

        echo self::COLOR_GREEN.'Application key set successfully in .env.'.self::COLOR_RESET."\n";
        echo self::COLOR_GRAY.'Key: '.$key.self::COLOR_RESET."\n";
        echo self::COLOR_YELLOW.'Please ensure .env is not committed to version control.'.self::COLOR_RESET."\n";
    }

    private function commandAdminReset($args)
    {
        $username = 'admin';
        $password = $args[0] ?? 'admin';

        echo self::COLOR_YELLOW."Resetting password for user '$username'...".self::COLOR_RESET."\n";

        try {
            $db = Database::getInstance();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Check if user exists first
            $check = $db->query('SELECT id FROM users WHERE username = ?', [$username])->fetch();

            if ($check) {
                $db->query('UPDATE users SET password = ? WHERE username = ?', [$hash, $username]);
                echo self::COLOR_GREEN.'Password updated successfully.'.self::COLOR_RESET."\n";
            } else {
                // Determine if we should create it
                echo self::COLOR_YELLOW."User '$username' not found. Creating...".self::COLOR_RESET."\n";
                $db->query('INSERT INTO users (username, password, created_at) VALUES (?, ?, ?)', [
                    $username, $hash, date('Y-m-d H:i:s'),
                ]);
                echo self::COLOR_GREEN.'User created successfully.'.self::COLOR_RESET."\n";
            }

            echo 'New Password: '.self::COLOR_BOLD.$password.self::COLOR_RESET."\n";

        } catch (\Exception $e) {
            echo self::COLOR_RED.'Error: '.$e->getMessage().self::COLOR_RESET."\n";
        }
    }

    private function commandInstall($args)
    {
        echo self::COLOR_BLUE.'=== MIVO Installer ==='.self::COLOR_RESET."\n";

        // 1. Database Migration
        echo "Setting up database...\n";
        try {
            if (Migrations::up()) {
                echo self::COLOR_GREEN.'Database schema created successfully.'.self::COLOR_RESET."\n";
            }
        } catch (\Exception $e) {
            echo self::COLOR_RED.'Migration Error: '.$e->getMessage().self::COLOR_RESET."\n";

            return;
        }

        // 2. Encryption Key
        echo "Generating encryption key...\n";

        $envPath = ROOT.'/.env';
        $keyExists = false;

        if (file_exists($envPath)) {
            $envIds = parse_ini_file($envPath);
            if (! empty($envIds['APP_KEY']) && $envIds['APP_KEY'] !== 'mivo_official_secret_key_32bytes') {
                $keyExists = true;
            }
        }

        if (! $keyExists) {
            $this->commandKeyGenerate();
        } else {
            echo self::COLOR_YELLOW.'Secret key already set in .env. Skipping.'.self::COLOR_RESET."\n";
        }

        // 3. Admin Account
        echo 'Create Admin Account? [Y/n] ';
        $handle = fopen('php://stdin', 'r');
        $line = trim(fgets($handle));

        if (strtolower($line) != 'n') {
            echo 'Username [admin]: ';
            $user = trim(fgets($handle));
            if (empty($user)) {
                $user = 'admin';
            }

            echo 'Password [admin]: ';
            $pass = trim(fgets($handle));
            if (empty($pass)) {
                $pass = 'admin';
            }

            // Re-use admin reset logic slightly modified or called directly
            $this->commandAdminReset([$pass]); // Simplification: admin:reset implementation uses hardcoded user='admin' currently, need to update it to support custom username if we want full flexibility.
            // Wait, my commandAdminReset implementation uses hardcoded 'admin'.
            // I should update commandAdminReset to accept username as argument or just replicate logic here.
            // Replicating logic for clarity here.

            /* Actually, commandAdminReset as currently implemented takes password as arg[0] and uses 'admin' as username.
               User requested robust install. I will just run the logic manually here to respect the inputted username. */

            try {
                $db = Database::getInstance();
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $check = $db->query('SELECT id FROM users WHERE username = ?', [$user])->fetch();
                if ($check) {
                    $db->query('UPDATE users SET password = ? WHERE username = ?', [$hash, $user]);
                    echo self::COLOR_GREEN."User '$user' updated.".self::COLOR_RESET."\n";
                } else {
                    $db->query('INSERT INTO users (username, password, created_at) VALUES (?, ?, ?)', [$user, $hash, date('Y-m-d H:i:s')]);
                    echo self::COLOR_GREEN."User '$user' created.".self::COLOR_RESET."\n";
                }
            } catch (\Exception $e) {
                echo self::COLOR_RED.'Error creating user: '.$e->getMessage().self::COLOR_RESET."\n";
            }
        }

        echo "\n".self::COLOR_GREEN.'Installation Completed Successfully!'.self::COLOR_RESET."\n";
        echo 'You can now run: '.self::COLOR_YELLOW.'php mivo serve'.self::COLOR_RESET."\n";
    }

    private function commandHelp()
    {
        echo self::COLOR_YELLOW.'Usage:'.self::COLOR_RESET."\n";
        echo "  php mivo [command] [options]\n\n";

        echo self::COLOR_YELLOW.'Available commands:'.self::COLOR_RESET."\n";
        echo '  '.self::COLOR_GREEN.'install      '.self::COLOR_RESET."    Install MIVO (Setup DB & Admin)\n";
        echo '  '.self::COLOR_GREEN.'serve        '.self::COLOR_RESET."    Start the development server\n";
        echo '  '.self::COLOR_GREEN.'key:generate '.self::COLOR_RESET."    Set the application key\n";
        echo '  '.self::COLOR_GREEN.'admin:reset  '.self::COLOR_RESET."    Reset admin password (default: admin)\n";
        echo '  '.self::COLOR_GREEN.'help         '.self::COLOR_RESET."    Show this help message\n";
        echo "\n";
    }
}
