<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function check()
    {
        $versionJsonPath = base_path('version.json');
        $versionData = [];
        if (file_exists($versionJsonPath)) {
            $versionData = json_decode(file_get_contents($versionJsonPath), true) ?? [];
        }

        $apkPath = public_path('downloads/app-release.apk');
        $hasApk = file_exists($apkPath);

        if (!$hasApk) {
            $altPath = base_path('mobile-gateway/build/app/outputs/flutter-apk/app-release.apk');
            if (file_exists($altPath)) {
                $hasApk = true;
                $apkPath = $altPath;
            }
        }

        $fileSizeMb = $hasApk ? round(filesize($apkPath) / (1024 * 1024), 1) : 0;
        $lastModified = $hasApk ? date('d/m/Y H:i', filemtime($apkPath)) : null;

        $version = $versionData['latest_version'] ?? config('app.apk_version', '1.0.7');
        $code = $versionData['version_code'] ?? config('app.apk_version_code', 6);
        $notes = $versionData['release_notes'] ?? 'Përmirësime në SMS Gateway, sinkronizim i ri i takimeve dhe lokalizim dypalësh Shqip & Anglisht.';

        $downloadUrl = url('download/apk');

        return response()->json([
            'latest_version' => (string) $version,
            'version_code' => (int) $code,
            'has_apk' => $hasApk,
            'file_size_mb' => $fileSizeMb,
            'last_modified' => $lastModified,
            'download_url' => $downloadUrl,
            'release_notes' => (string) $notes,
        ]);
    }

    public function download()
    {
        $apkPath = public_path('downloads/app-release.apk');
        if (!file_exists($apkPath)) {
            $apkPath = base_path('mobile-gateway/build/app/outputs/flutter-apk/app-release.apk');
        }

        if (!file_exists($apkPath)) {
            abort(404, 'Nuk u gjet asnjë skedar APK në server.');
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $fileName = 'E4ProTech-Engine.apk';
        $fileSize = filesize($apkPath);

        return response()->streamDownload(function () use ($apkPath) {
            $stream = fopen($apkPath, 'rb');
            if ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 1024 * 64);
                    flush();
                }
                fclose($stream);
            }
        }, $fileName, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Length' => (string) $fileSize,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ]);
    }
}
