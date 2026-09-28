<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function check()
    {
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

        return response()->json([
            'latest_version' => config('app.apk_version', '1.0.2'),
            'version_code' => (int) config('app.apk_version_code', 3),
            'has_apk' => $hasApk,
            'file_size_mb' => $fileSizeMb,
            'last_modified' => $lastModified,
            'download_url' => url('download/apk'),
            'release_notes' => 'Përmirësime në SMS Gateway, sinkronizim i ri i takimeve dhe lokalizim dypalësh Shqip & Anglisht.',
        ]);
    }

    public function download()
    {
        $apkPath = public_path('downloads/app-release.apk');
        if (!file_exists($apkPath)) {
            $altPath = base_path('mobile-gateway/build/app/outputs/flutter-apk/app-release.apk');
            if (file_exists($altPath)) {
                $apkPath = $altPath;
            } else {
                abort(404, 'Nuk u gjet asnjë skedar APK në server.');
            }
        }

        return response()->download($apkPath, 'E4ProTech-Gateway.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
