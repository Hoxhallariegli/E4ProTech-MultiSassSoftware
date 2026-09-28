# ==============================================================================
# E4ProTech - Automated APK Release & Deployment Script
# Usage:
#   powershell -ExecutionPolicy Bypass -File .\release-apk.ps1
#   powershell -ExecutionPolicy Bypass -File .\release-apk.ps1 1.1.0
# ==============================================================================

param (
    [string]$NewVersion = ""
)

# Detect root directory
$rootDir = $PSScriptRoot
if (Test-Path "$PSScriptRoot\..\.env") {
    $rootDir = (Resolve-Path "$PSScriptRoot\..").Path
}

Write-Host "`n>>> [1/5] Checking current version from .env..." -ForegroundColor Cyan

$envPath = "$rootDir\.env"
if (-not (Test-Path $envPath)) {
    Write-Host "Error: .env file not found at $envPath" -ForegroundColor Red
    exit 1
}

$envContent = Get-Content $envPath -Raw
$currentVer = "1.0.2"
$currentCode = 3

if ($envContent -match 'APK_VERSION=(.+)') {
    $currentVer = $matches[1].Trim()
}
if ($envContent -match 'APK_VERSION_CODE=(.+)') {
    $currentCode = [int]$matches[1].Trim()
}

$nextCode = $currentCode + 1
$nextVer = ""

if ($NewVersion -ne "") {
    $nextVer = $NewVersion.Trim()
} else {
    # Auto-increment patch version (e.g. 1.0.2 -> 1.0.3)
    $parts = $currentVer.Split('.')
    if ($parts.Count -eq 3) {
        $major = $parts[0]
        $minor = $parts[1]
        $patch = [int]$parts[2] + 1
        $nextVer = "$major.$minor.$patch"
    } else {
        $nextVer = "$currentVer.1"
    }
}

Write-Host "   * Current Version: v$currentVer (Build $currentCode)" -ForegroundColor Yellow
Write-Host "   * New Release:     v$nextVer (Build $nextCode)" -ForegroundColor Green

# 2. Update .env file
Write-Host "`n>>> [2/5] Updating .env and pubspec.yaml with new version..." -ForegroundColor Cyan

if ($envContent -match 'APK_VERSION=') {
    $envContent = $envContent -replace 'APK_VERSION=.+', "APK_VERSION=$nextVer"
} else {
    $envContent += "`nAPK_VERSION=$nextVer"
}

if ($envContent -match 'APK_VERSION_CODE=') {
    $envContent = $envContent -replace 'APK_VERSION_CODE=.+', "APK_VERSION_CODE=$nextCode"
} else {
    $envContent += "`nAPK_VERSION_CODE=$nextCode"
}

Set-Content -Path $envPath -Value $envContent

# Update pubspec.yaml version
$pubspecPath = "$rootDir\mobile-gateway\pubspec.yaml"
if (Test-Path $pubspecPath) {
    $pubspecContent = Get-Content $pubspecPath -Raw
    $pubspecContent = $pubspecContent -replace 'version:\s*.+', "version: $nextVer+$nextCode"
    Set-Content -Path $pubspecPath -Value $pubspecContent
}

# 3. Build Release APK
Write-Host "`n>>> [3/5] Building Release APK with Flutter..." -ForegroundColor Cyan
$env:ANDROID_PREFS_ROOT = $null

Push-Location "$rootDir\mobile-gateway"
try {
    flutter build apk --release
} finally {
    Pop-Location
}

$builtApk = "$rootDir\mobile-gateway\build\app\outputs\flutter-apk\app-release.apk"
if (-not (Test-Path $builtApk)) {
    Write-Host "Error: Flutter build failed. APK not found at $builtApk" -ForegroundColor Red
    exit 1
}

# 4. Copy APK to public/downloads
Write-Host "`n>>> [4/5] Copying APK to public/downloads/app-release.apk..." -ForegroundColor Cyan
$publicDownloads = "$rootDir\public\downloads"
if (-not (Test-Path $publicDownloads)) {
    New-Item -ItemType Directory -Path $publicDownloads | Out-Null
}

Copy-Item -Path $builtApk -Destination "$publicDownloads\app-release.apk" -Force

# 5. Clear Laravel Cache
Write-Host "`n>>> [5/5] Clearing Laravel configuration cache..." -ForegroundColor Cyan
Push-Location "$rootDir"
try {
    php artisan config:clear
    php artisan view:clear
} finally {
    Pop-Location
}

Write-Host "`n========================================================" -ForegroundColor Green
Write-Host "SUCCESS: NEW APK RELEASE v$nextVer (Build $nextCode) PUBLISHED!" -ForegroundColor Green
Write-Host "========================================================" -ForegroundColor Green
Write-Host "* APK Path: $publicDownloads\app-release.apk" -ForegroundColor White
Write-Host "* Public Download URL: /download/apk" -ForegroundColor White
Write-Host "* Users will now be notified of the new update v$nextVer in the app!`n" -ForegroundColor White
