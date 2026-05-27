<?php

namespace App\Core;

class Hooks
{
    /**
     * @var array Stores all registered actions
     */
    private static $actions = [];

    /**
     * @var array Stores all registered filters
     */
    private static $filters = [];

    /**
     * Register a new action
     *
     * @param  string  $tag  The name of the action hook
     * @param  callable  $callback  The function to call
     * @param  int  $priority  Lower numbers correspond to earlier execution
     * @param  int  $accepted_args  The number of arguments the function accepts
     */
    public static function addAction($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        self::$actions[$tag][$priority][] = [
            'function' => $callback,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Execute an action
     *
     * @param  string  $tag  The name of the action hook
     * @param  mixed  ...$args  Optional arguments to pass to the callback
     */
    public static function doAction($tag, ...$args)
    {
        if (empty(self::$actions[$tag])) {
            return;
        }

        // Sort by priority
        ksort(self::$actions[$tag]);

        foreach (self::$actions[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callbackData) {
                call_user_func_array($callbackData['function'], array_slice($args, 0, $callbackData['accepted_args']));
            }
        }
    }

    /**
     * Register a new filter
     *
     * @param  string  $tag  The name of the filter hook
     * @param  callable  $callback  The function to call
     * @param  int  $priority  Lower numbers correspond to earlier execution
     * @param  int  $accepted_args  The number of arguments the function accepts
     */
    public static function addFilter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        self::$filters[$tag][$priority][] = [
            'function' => $callback,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Apply filters to a value
     *
     * @param  string  $tag  The name of the filter hook
     * @param  mixed  $value  The value to be filtered
     * @param  mixed  ...$args  Optional extra arguments
     * @return mixed The filtered value
     */
    public static function applyFilters($tag, $value, ...$args)
    {
        if (empty(self::$filters[$tag])) {
            return $value;
        }

        // Sort by priority
        ksort(self::$filters[$tag]);

        foreach (self::$filters[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callbackData) {
                // Prepend value to args
                $params = array_merge([$value], array_slice($args, 0, $callbackData['accepted_args'] - 1));
                $value = call_user_func_array($callbackData['function'], $params);
            }
        }

        return $value;
    }

    /**
     * Check if any action has been registered for a hook.
     *
     * @param  string  $tag  The name of the action hook.
     * @return bool True if action exists, false otherwise.
     */
    public static function hasAction($tag)
    {
        return isset(self::$actions[$tag]);
    }

    /**
     * Check if any filter has been registered for a hook.
     *
     * @param  string  $tag  The name of the filter hook.
     * @return bool True if filter exists, false otherwise.
     */
    public static function hasFilter($tag)
    {
        return isset(self::$filters[$tag]);
    }
}
