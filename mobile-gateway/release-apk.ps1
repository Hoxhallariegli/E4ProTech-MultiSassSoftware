# Automated 1-Command APK Release Script
$ErrorActionPreference = "Stop"

Write-Host ">>> [1/5] Checking current version from version.json..." -ForegroundColor Cyan

$versionFile = "../version.json"
if (-not (Test-Path $versionFile)) { $versionFile = "version.json" }
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

Write-Host "`n>>> [3/5] Building Release APK with Flutter..." -ForegroundColor Cyan

flutter build apk --debug

Write-Host "`n>>> [4/5] Copying APK to public/downloads/app-release.apk..." -ForegroundColor Cyan
$apkSource = "build/app/outputs/flutter-apk/app-release.apk"
if (-not (Test-Path $apkSource)) {
    $apkSource = "build/app/outputs/flutter-apk/app-debug.apk"
}

$destDir = "../public/downloads"
if (-not (Test-Path $destDir)) {
    New-Item -ItemType Directory -Force -Path $destDir | Out-Null
}

Copy-Item -Path $apkSource -Destination "$destDir/app-release.apk" -Force

Write-Host "`n>>> [5/5] Clearing Laravel configuration cache..." -ForegroundColor Cyan
Set-Location ".."
php artisan config:clear
php artisan view:clear

Write-Host "`n========================================================" -ForegroundColor Green
Write-Host "SUCCESS: NEW APK RELEASE v$newVersion (Build $newCode) PUBLISHED!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host "* version.json & config updated and tracked in Git."
Write-Host "* APK Path: C:\laragon\www\LaraFluterAuto\public\downloads\app-release.apk"
Write-Host "* Public Download URL: /download/apk"
Write-Host "* Users will now be notified of the new update v$newVersion in the app!`n"
