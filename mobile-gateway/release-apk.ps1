# Automated 1-Command APK Release Script
$ErrorActionPreference = "Stop"

# Set explicit Java 17 JDK & Standard Android SDK
$env:JAVA_HOME = "C:\Program Files\Eclipse Adoptium\jdk-17.0.14.7-hotspot"
$env:ANDROID_HOME = "C:\Users\Admin\AppData\Local\Android\sdk"
$env:PATH = "$env:JAVA_HOME\bin;$env:PATH"

Remove-Item Env:\ANDROID_USER_HOME -ErrorAction SilentlyContinue
Remove-Item Env:\GRADLE_USER_HOME -ErrorAction SilentlyContinue

Write-Host ">>> [1/5] Checking current version from version.json..." -ForegroundColor Cyan

$versionFile = "../version.json"
$versionData = Get-Content $versionFile -Raw | ConvertFrom-Json

$currentVersion = $versionData.latest_version
$currentCode = [int]$versionData.version_code

$versionParts = $currentVersion.Split('.')
$major = [int]$versionParts[0]
$minor = [int]$versionParts[1]
$patch = [int]$versionParts[2]

$newPatch = $patch + 1
$newCode = $currentCode + 1
$newVersion = "$major.$minor.$newPatch"

Write-Host "   * Current Version: v$currentVersion (Build $currentCode)" -ForegroundColor Yellow
Write-Host "   * New Release:     v$newVersion (Build $newCode)" -ForegroundColor Green

Write-Host "`n>>> [2/5] Updating version.json, .env, and pubspec.yaml with new version..." -ForegroundColor Cyan

$versionData.latest_version = $newVersion
$versionData.version_code = $newCode
$versionData | ConvertTo-Json -Depth 5 | Set-Content $versionFile -Encoding UTF8

$envFile = "../.env"
if (Test-Path $envFile) {
    $envContent = Get-Content $envFile -Raw
    $envContent = $envContent -replace 'APK_VERSION=.*', "APK_VERSION=$newVersion"
    $envContent = $envContent -replace 'APK_VERSION_CODE=.*', "APK_VERSION_CODE=$newCode"
    Set-Content $envFile -Value $envContent -Encoding UTF8
}

$pubspecFile = "pubspec.yaml"
if (Test-Path $pubspecFile) {
    $pubspecContent = Get-Content $pubspecFile -Raw
    $pubspecContent = $pubspecContent -replace 'version: .*', "version: $newVersion+$newCode"
    Set-Content $pubspecFile -Value $pubspecContent -Encoding UTF8
}

Write-Host "`n>>> [3/5] Cleaning and Building Release APK with Flutter (Java 17)..." -ForegroundColor Cyan

flutter clean
flutter pub get
flutter build apk --release

Write-Host "`n>>> [4/5] Copying APK to D:\Share\Apk and public/downloads..." -ForegroundColor Cyan
$apkSource = "build/app/outputs/flutter-apk/app-release.apk"

# Destination 1: D:\Share\Apk
$shareDir = "D:\Share\Apk"
if (-not (Test-Path $shareDir)) {
    New-Item -ItemType Directory -Force -Path $shareDir | Out-Null
}
Copy-Item -Path $apkSource -Destination "$shareDir/app-release.apk" -Force
Write-Host "   * Copied to $shareDir/app-release.apk" -ForegroundColor Green

# Destination 2: public/downloads
$destDir = "../public/downloads"
if (-not (Test-Path $destDir)) {
    New-Item -ItemType Directory -Force -Path $destDir | Out-Null
}
Copy-Item -Path $apkSource -Destination "$destDir/app-release.apk" -Force
Write-Host "   * Copied to $destDir/app-release.apk" -ForegroundColor Green

Write-Host "`n>>> [5/5] Clearing Laravel configuration cache..." -ForegroundColor Cyan
Set-Location ".."
php artisan config:clear
php artisan view:clear

Write-Host "`n========================================================" -ForegroundColor Green
Write-Host "SUCCESS: NEW APK RELEASE v$newVersion (Build $newCode) PUBLISHED!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host "* version.json & config updated."
Write-Host "* APK Copied to: D:\Share\Apk\app-release.apk"
Write-Host "* APK Copied to: C:\laragon\www\LaraFluterAuto\public\downloads\app-release.apk"
Write-Host "* Users will now be notified of the new update v$newVersion in the app!`n"
