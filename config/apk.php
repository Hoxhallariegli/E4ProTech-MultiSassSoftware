<?php

$version = json_decode(
    file_get_contents(base_path('version.json')),
    true
);

return [
    'version' => $version['latest_version'] ?? null,
    'version_code' => $version['version_code'] ?? null,
];
