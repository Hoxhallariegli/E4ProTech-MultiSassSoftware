<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = ['en'];
        $langPath = lang_path();

        if (File::exists($langPath)) {
            foreach (File::directories($langPath) as $dir) {
                $lang = basename($dir);
                if (strlen($lang) <= 5 && !in_array($lang, $languages)) {
                    $languages[] = $lang;
                }
            }
            foreach (File::files($langPath) as $file) {
                if ($file->getExtension() === 'json') {
                    $lang = str_replace('.json', '', $file->getFilename());
                    if (strlen($lang) <= 5 && !in_array($lang, $languages)) {
                        $languages[] = $lang;
                    }
                }
            }
        }

        sort($languages);

        // Map code to labels for UI
        $map = [
            'en' => 'English',
            'sq' => 'Shqip',
            'it' => 'Italiano',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
        ];

        $data = array_map(fn($lang) => [
            'code' => $lang,
            'name' => $map[$lang] ?? strtoupper($lang),
        ], $languages);

        return response()->json([
            'data' => $data
        ]);
    }
}
