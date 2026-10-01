<?php

declare(strict_types=1);

$versionJsonPath = __DIR__ . '/../version.json';
$versionData = [];
if (file_exists($versionJsonPath)) {
    $versionData = json_decode(file_get_contents($versionJsonPath), true) ?? [];
}

return [

    'user_agent' => env('USER_AGENT', ''),

    // Priority 1: version.json (automatically updated on git pull)
    // Priority 2: .env file (if version.json is missing)
    'apk_version' => $versionData['latest_version'] ?? env('APK_VERSION', '1.0.77'),
    'apk_version_code' => (int) ($versionData['version_code'] ?? env('APK_VERSION_CODE', 76)),

];
