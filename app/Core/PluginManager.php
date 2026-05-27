<?php

namespace App\Core;

class PluginManager
{
    /**
     * @var string Path to plugins directory
     */
    private $pluginsDir;

    /**
     * @var array List of active plugins
     */
    private $activePlugins = [];

    public function __construct()
    {
        $this->pluginsDir = dirname(__DIR__, 2).'/plugins'; // Root/plugins
    }

    /**
     * Load all active plugins
     */
    public function loadPlugins()
    {
        // Ensure plugins directory exists
        if (! is_dir($this->pluginsDir)) {
            return;
        }

        // 1. Get List of Active Plugins (For now, we load ALL folders as active)
        // TODO: Implement database/config check for active status
        $plugins = scandir($this->pluginsDir);

        foreach ($plugins as $pluginName) {
            if ($pluginName === '.' || $pluginName === '..') {
                continue;
            }

            $pluginPath = $this->pluginsDir.'/'.$pluginName;

            // Check if it is a directory and has specific plugin file
            if (is_dir($pluginPath) && file_exists($pluginPath.'/plugin.php')) {
                $this->loadPlugin($pluginName, $pluginPath);
            }
        }

        // Fire 'plugins_loaded' action after all plugins are loaded
        Hooks::doAction('plugins_loaded');
    }

    /**
     * Load a single plugin
     *
     * @param  string  $name  Plugin folder name
     * @param  string  $path  Full path to plugin directory
     */
    private function loadPlugin($name, $path)
    {
        try {
            require_once $path.'/plugin.php';
            $this->activePlugins[] = $name;
        } catch (\Exception $e) {
            error_log("Failed to load plugin [$name]: ".$e->getMessage());
        }
    }

    /**
     * Get list of loaded plugins
     *
     * @return array
     */
    public function getActivePlugins()
    {
        return $this->activePlugins;
    }
}
