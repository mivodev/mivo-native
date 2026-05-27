<?php

namespace App\Core;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            // Convert namespace to full file path
            // App\Core\Router -> app/Core/Router.php
            // We assume ROOT is defined externally
            if (! defined('ROOT')) {
                return;
            }

            $prefix = 'App\\';
            $base_dir = ROOT.'/app/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = substr($class, $len);
            $file = $base_dir.str_replace('\\', '/', $relative_class).'.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}
