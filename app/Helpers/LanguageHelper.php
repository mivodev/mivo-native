<?php

namespace App\Helpers;

use App\Core\Hooks;

class LanguageHelper
{
    /**
     * Get list of available languages from public/lang directory
     *
     * @return array Array of languages with code and name
     */
    public static function getAvailableLanguages()
    {
        $langDir = ROOT.'/public/lang';
        $languages = [];

        if (! is_dir($langDir)) {
            return [];
        }

        $files = scandir($langDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $code = pathinfo($file, PATHINFO_FILENAME);

                // Read file to get language name if defined, otherwise use code
                $content = file_get_contents($langDir.'/'.$file);
                $data = json_decode($content, true);

                $name = $data['_meta']['name'] ?? strtoupper($code);
                $flag = $data['_meta']['flag'] ?? '🌐';

                $languages[] = [
                    'code' => $code,
                    'name' => $name,
                    'flag' => $flag,
                ];
            }
        }

        return Hooks::applyFilters('get_available_languages', $languages);
    }
}
