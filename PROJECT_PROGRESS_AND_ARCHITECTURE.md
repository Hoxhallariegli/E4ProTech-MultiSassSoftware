# E4ProTech Engine - Project Progress & Technical Architecture Documentation

## 1. Executive Summary & File Location
- **Documentation File Path:** `C:\laragon\www\LaraFluterAuto\PROJECT_PROGRESS_AND_ARCHITECTURE.md`
- **Project Name:** E4ProTech Engine (Multi-Tenant SaaS Management Platform & Mobile Gateway)
- **Production Domain:** `https://app.e4protech.com`
- **System Architecture:**
  - **Backend:** Laravel 12 (PHP 8.3) REST API & Livewire Admin Panel
  - **Mobile App:** Flutter (Cross-platform Android / iOS / Web)
  - **Push Engine:** Firebase Cloud Messaging (FCM REST API v1 - 100% Hostinger / Shared Hosting Compatible)
  - **SMS Engine:** Android Gateway Device with Native SIM Sending (`is_sms_gateway`)
  - **Public Portal:** Multi-Tenant Salon Landing Pages (`/s/{slug}`) with Livewire Online Booking

---

## 2. Comprehensive Accomplishments & Implemented Systems

### 🔥 A. 100% Shared-Hosting Compatible Firebase FCM Push Engine
- **Per-Shop Token & User Isolation:**
  - Notifications are strictly isolated per salon. `FirebaseService::sendToShop` sends notifications directly to FCM tokens matching the `barber_shop_id` or to users belonging to that salon.
- **Silent Data Messages for SMS Triggers (`sendDataMessage`):**
  - FCM SMS Gateway triggers (`action == 'SEND_SMS'`) are sent as high-priority SILENT DATA messages without an FCM `notification` header. This prevents Android OS from displaying unwanted "SMS Gateway: Duke dërguar SMS..." popup banners while allowing Flutter to send the SMS silently in the background.
- **Event-Driven Notification Router & Observer Deduplication:**
  - `BookingObserver` / `CustomerObserver` ➔ `NotificationRouter` checks `event_settings` to see if notifications are enabled for that salon.
  - `NotificationRouter` contains static deduplication (`static::$processedEvents`) so duplicate events within the same request are handled exactly ONCE.
- **Global Observer Registration:**
  - All observers (`BookingObserver`, `CustomerObserver`, `PaymentObserver`, `ServiceObserver`, `BarberObserver`, `BarberShopObserver`) are registered in `AppServiceProvider.php` to guarantee they fire on every Eloquent action across the system.

### 📱 B. Designated Android SIM SMS Gateway, Background Service & Message Queues
- **Background Foreground Service (`FlutterBackgroundService`):**
  - Uses `flutter_background_service` and `telephony` with a dedicated Android 14+ Foreground Service (`sms_gateway` channel) to guarantee reliable SIM SMS sending in foreground and background.
  - Android 14+ declaration in `AndroidManifest.xml`:
    - Permission: `android.permission.FOREGROUND_SERVICE_SPECIAL_USE`
    - Service: `<service android:name="id.flutter.flutter_background_service.BackgroundService" android:foregroundServiceType="specialUse" android:exported="true" tools:replace="android:exported"><property android:name="android.app.PROPERTY_SPECIAL_USE_FGS_TYPE" android:value="SMS Gateway for barber shop appointments reminder and notifications" /></service>`
- **Global Gateway Device Fallback:**
  - `SendSmsToGatewayJob` and `SendFirebaseNotificationListener` first search for a gateway token assigned to the salon or staff of that salon. If not found, it falls back to ANY active device marked with `is_sms_gateway = true` in the system, and handles offline states gracefully without throwing unhandled exceptions.
- **Dynamic SMS Reminders (`reminder_hours_before`):**
  - `barber_shops` table contains `reminder_hours_before` (default: 2 hours).
  - Mobile App Settings UI includes a dynamic Dropdown Selector (1h, 2h, 4h, 12h, 24h, 48h) updating the salon's reminder interval.
  - Scheduled Artisan Command `php artisan sms:send-reminders` runs every 5 minutes in `routes/console.php` to queue upcoming reminder SMS messages.
- **In-App Message Logs Viewer:**
  - Direct shortcut button in Settings opening `MessageLogListPage` with real-time delivery status and local SharedPreferences logs (`sms_logs`).

### 🛠️ C. Java 17 JDK & Automated Release Script (`release-apk.ps1`)
- **Java 17 JDK Environment Setup:**
  - **Installed Location:** `C:\Program Files\Eclipse Adoptium\jdk-17.0.14.7-hotspot`
  - **Flutter JDK Config Command:** `flutter config --jdk-dir="C:\Program Files\Eclipse Adoptium\jdk-17.0.14.7-hotspot"`
  - **PowerShell Session Setup:**
    ```powershell
    $env:JAVA_HOME = "C:\Program Files\Eclipse Adoptium\jdk-17.0.14.7-hotspot"
    $env:PATH = "$env:JAVA_HOME\bin;$env:PATH"
    ```
- **Automated 1-Command Build Script (`release-apk.ps1`):**
  - **Command:** `powershell -ExecutionPolicy Bypass -File .\release-apk.ps1`
  - **Automated Steps:**
    1. Reads version from `version.json` and increments build code and patch version.
    2. Updates `version.json`, `.env`, and `mobile-gateway/pubspec.yaml`.
    3. Sets Java 17 JDK environment variables.
    4. Runs `flutter build apk --release --no-pub`.
    5. Copies built APK to `public/downloads/app-release.apk`.
    6. Clears Laravel config and view caches (`php artisan config:clear`, `php artisan view:clear`).

### 🌐 D. Public Landing Pages & Interactive Online Booking (`/s/{slug}`)
- **Multi-Tenant Landing Pages:**
  - Directory on Homepage (`welcome.blade.php`) listing all active salons:
    - 💈 `Gentlemen Barber Shop` (`/s/gentlemen-barber-shop`)
    - 💇‍♀️ `Elegance Beauty Salon` (`/s/elegance-beauty-salon`)
    - 💅 `Glamour Nail Studio` (`/s/glamour-nail-studio`)
- **Dynamic `min_service_time` Slot Calculation:**
  - `BarberShop` model computes `resolved_min_service_time` using `Service::withoutGlobalScope('barber_shop_access')` to prevent falling back to the 15-minute default when accessed from outside the active tenant context.
- **Livewire 3 Booking Component (`PublicShopBooking`):**
  - Live validation, service/staff selection, date/time picker with overlap check (`Booking::checkOverlap`).
  - Automatically creates customer, creates booking record, and triggers the full notification pipeline.

---

## 3. Key Project Directory Structure

```
LaraFluterAuto/
├── PROJECT_PROGRESS_AND_ARCHITECTURE.md # Main documentation file
├── app/
│   ├── Console/Commands/                 # Scheduled Commands (SendQueuedSmsMessages, SendBookingReminders)
│   ├── Http/Controllers/Api/Mobile/     # Mobile REST API Controllers (DeviceToken, AppVersion, MessageQueue, BusinessSettingsController)
│   ├── Http/Controllers/ShopLandingController.php # Public landing page controller (/s/{slug})
│   ├── Jobs/SendSmsToGatewayJob.php      # SMS Gateway Dispatch Job with Fallback
│   ├── Listeners/SendFirebaseNotificationListener.php # FCM Push & Silent Data SMS Queue Event Listener
│   ├── Livewire/PublicShopBooking.php    # Livewire Public Online Booking Component
│   ├── Models/                           # Eloquent Models with Spatie Tenancy scope
│   ├── Observers/                        # Auto-sync observers (BookingObserver, BarberShopObserver, etc.)
│   ├── Providers/AppServiceProvider.php  # Global Observer Registration & Event Listeners
│   └── Services/                         # FirebaseService (FCM REST v1), NotificationRouter
├── database/migrations/                  # Database migrations (device_tokens, settings, customers, reminder_hours_before)
├── mobile-gateway/                       # Flutter Application Root
│   ├── android/app/src/main/AndroidManifest.xml # Android permissions & BackgroundService declaration
│   └── lib/
│       ├── core/branding/branding_cubit.dart # Branding state with reminder_hours_before
│       ├── core/notifications/push_service.dart # FCM init, BackgroundService, Telephony SMS handler
│       ├── modules/auth/presentation/pages/login_page.dart # Login with auto-fill chips
│       ├── modules/dashboard/            # Modules (booking, customer, payment, message_log, etc.)
│       ├── modules/settings/presentation/pages/settings_page.dart # Settings & In-App APK Downloader & SMS Timer Dropdown
│       └── main.dart                     # App entry point
├── public/downloads/app-release.apk      # Compiled Release APK file
├── release-apk.ps1                       # Automated 1-command APK release script
└── version.json                          # Version tracker file (Git tracked for host deployment)
```

---

## 4. Instructions for Starting a New Chat
When you start a new chat session, you can simply paste or refer to this summary:
> *"The project documentation and architecture are saved in `C:\laragon\www\LaraFluterAuto\PROJECT_PROGRESS_AND_ARCHITECTURE.md`. The system uses Laravel 12 + Livewire + Flutter built with Java 17 JDK (`C:\Program Files\Eclipse Adoptium\jdk-17.0.14.7-hotspot`), 100% Firebase FCM Push Notifications (isolated per shop/user), Android SIM SMS Gateway using `FlutterBackgroundService` and `telephony`, and In-App APK Updates via `release-apk.ps1`."*
