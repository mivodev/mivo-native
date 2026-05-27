<?php

namespace App\Helpers;

class FlashHelper
{
    const SESSION_KEY = 'flash_notification';

    /**
     * Set a flash message.
     *
     * @param  string  $type  Notification type: 'success', 'error', 'warning', 'info', 'question'
     * @param  string  $title  Title of the notification (or i18n key)
     * @param  string  $message  (Optional) Body text of the notification (or i18n key)
     * @param  array  $params  (Optional) Parameters for translation interpolation
     * @param  bool  $isTranslated  Whether to treat title and message as translation keys
     */
    public static function set($type, $title, $message = null, $params = [], $isTranslated = false)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[self::SESSION_KEY] = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'params' => $params,
            'isTranslated' => $isTranslated,
        ];
    }

    /**
     * Check if a flash message exists.
     *
     * @return bool
     */
    public static function has()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Get the flash message and clear it from session.
     *
     * @return array|null
     */
    public static function get()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (self::has()) {
            $notification = $_SESSION[self::SESSION_KEY];
            unset($_SESSION[self::SESSION_KEY]);

            return $notification;
        }

        return null;
    }
}
