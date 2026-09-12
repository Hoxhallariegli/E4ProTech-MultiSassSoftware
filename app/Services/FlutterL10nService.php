<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FlutterL10nService
{
    /**
     * Syncs a Laravel translation array to Flutter .dart files.
     */
    public function syncModule(string $moduleKebab, array $translations, string $lang): void
    {
        $l10nDir = base_path('mobile-gateway/lib/l10n');
        if (!File::exists($l10nDir)) {
            return;
        }

        $snake = Str::snake(Str::singular($moduleKebab));
        $path = "{$l10nDir}/{$snake}_{$lang}.dart";

        $entries = [];
        foreach ($translations as $key => $value) {
            $escapedKey = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $key);
            $escapedValue = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value);
            $entries[] = "  '{$escapedKey}': '{$escapedValue}',";
        }

        $content = "const Map<String, String> {$snake}" . ucfirst($lang) . " = {\n" . implode("\n", $entries) . "\n};\n";
        File::put($path, $content);

        // Ensure localization helper exists
        $localizerPath = "{$l10nDir}/{$snake}_localization.dart";
        if (!File::exists($localizerPath)) {
            $this->generateLocalizer($localizerPath, $snake);
        }
    }

    protected function generateLocalizer(string $path, string $snake): void
    {
        $locales = ['en', 'sq'];
        if (class_exists(\App\Models\Setting::class)) {
            $setting = \App\Models\Setting::where('key', 'supported_locales')->value('value');
            if ($setting) {
                $locales = json_decode($setting, true) ?: ['en', 'sq'];
            }
        }

        $imports = "";
        $cases = "";
        foreach ($locales as $lang) {
            $uLang = ucfirst($lang);
            $imports .= "import '{$snake}_{$lang}.dart';\n";
            $cases .= "    if (language == '$lang') return {$snake}{$uLang};\n";
        }

        $content = <<<DART
import 'package:flutter/widgets.dart';
$imports
String {$snake}Tr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
$cases    return {$snake}En;
  }

  final source = getSource();
  var value = source[key] ?? {$snake}En[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{\${name}}', replacement);
  });

  return value;
}
DART;
        // Fix the interpolation for Dart: replace {${name}} with {$name}
        $content = str_replace('{\${name}}', '{\$name}', $content);

        File::put($path, $content);
    }
}
