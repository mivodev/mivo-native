<?php

namespace App\Helpers;

class ErrorHelper
{
    public static function show($code = 404, $message = 'Page Not Found', $description = null)
    {
        http_response_code($code);

        // Provide default translation keys for common codes
        if ($description === null) {
            switch ($code) {
                case 403:
                    $message = ($message === 'Page Not Found') ? 'errors.403_title' : $message; // Override default if simple
                    $description = 'errors.403_desc';
                    break;
                case 500:
                    $message = ($message === 'Page Not Found') ? 'errors.500_title' : $message;
                    $description = 'errors.500_desc';
                    break;
                case 503:
                    $message = ($message === 'Page Not Found') ? 'errors.503_title' : $message;
                    $description = 'errors.503_desc';
                    break;
                case 404:
                default:
                    // If message is generic default, use key
                    if ($message === 'Page Not Found') {
                        $message = 'errors.404_title';
                    }
                    $description = 'errors.404_desc';
                    break;
            }
        }

        // Variables extracted in view
        $errorCode = $code;
        $errorMessage = $message;
        $errorDescription = $description;

        // Ensure strictly NO output before this if keeping clean, but we are in view mode.
        // Clean buffer if active to remove partial content
        if (ob_get_level()) {
            ob_end_clean();
        }

        require ROOT.'/app/Views/errors/default.php';
        exit;
    }

    public static function showException($exception)
    {
        http_response_code(500);

        // Clean output buffer to ensure clean error page
        if (ob_get_level()) {
            ob_end_clean();
        }

        require ROOT.'/app/Views/errors/development.php';
        exit;
    }
}
