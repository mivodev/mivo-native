<?php

namespace App\Helpers;

class ViewHelper
{
    /**
     * Render a generic badge with icon
     *
     * @param  string  $status  (active, locked, expired, limited, etc.)
     * @param  string|null  $label  Optional override text
     */
    public static function badge($status, $label = null)
    {
        // Define styles for each status key
        $styles = [
            'active' => ['class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400', 'icon' => 'check-circle'],
            'limited' => ['class' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400', 'icon' => 'pie-chart'],
            'locked' => ['class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400', 'icon' => 'lock'],
            'expired' => ['class' => 'bg-accents-2 text-accents-6', 'icon' => 'clock'],
            'default' => ['class' => 'bg-blue-100 text-blue-800', 'icon' => 'info'],
        ];

        $style = $styles[$status] ?? $styles['default'];
        $text = $label ?? ucfirst($status === 'limited' ? 'Quota' : $status);

        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s gap-1"><i data-lucide="%s" class="w-3 h-3"></i> %s</span>',
            $style['class'],
            $style['icon'],
            $text
        );
    }
}
