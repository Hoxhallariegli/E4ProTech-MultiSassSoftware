# 💈 BarberPro SaaS - Full Technical Specification & Data Flow

Ky dokument detajon çdo qelizë të sistemit, nga struktura e database deri te komunikimi ndërmjet Web-it, Serverit, Firebase dhe APK-së.

---

## 🏗️ 1. Arkitektura e Sistemit (System Architecture)

Sistemi operon në një cikël të mbyllur sinkronizimi:

1.  **Backend (Laravel 11):** Truri i sistemit. Menaxhon Database-in, Radhën (Queue), dhe API-të.
2.  **Web Dashboard (Livewire 3):** Ndërfaqja e menaxhimit për Super Admin (Ty) dhe Pronarët në PC.
3.  **Mobile App (Flutter):** Mjeti kryesor i punës. Punon në kohë reale me Reverb dhe merr urdhra dërgimi SMS nga Firebase.
4.  **Laravel Reverb:** Serveri WebSocket që mban të lidhur APK-në me Web-in (Refresh i menjëhershëm).
5.  **Firebase (FCM):** Transportuesi i "Reminders". Serveri i thotë Firebase -> Firebase i thotë APK-së -> APK dërgon SMS.

---

## 🗄️ 2. Database Schema e Detajuar (Full Table Structure)

### 🟢 Grupi 1: SaaS Core & Tenancy
#### Tabela: `barber_shops`
*   `id` (BigInt, PK)
*   `owner_id` (UUID, FK -> users)
*   `name` (String) - Emri zyrtar.
*   `app_name` (String) - Emri që shfaqet në APK.
*   `slug` (String, Unique) - URL-ja e sallonit.
*   `logo` (String, Nullable) - Path i logos.
*   `banner` (String, Nullable) - Imazhi kryesor.
*   `primary_color` (String) - Kodi HEX për APK-në.
*   `secondary_color` (String) - Kodi HEX.
*   `sms_enabled` (Boolean, Default: false).
*   `trial_ends_at` (Timestamp, Nullable).
*   `active` (Boolean).
*   `created_at`, `updated_at`.

#### Tabela: `plans`
*   `id`, `name`, `price`, `duration_months`, `max_barbers`, `max_services`, `active`.

#### Tabela: `subscriptions`
*   `id`, `barber_shop_id`, `plan_id`, `starts_at`, `ends_at`, `status` (trial, active, expired, cancelled).

---

### 🔵 Grupi 2: Menaxhimi i Sallonit (Business Operations)
#### Tabela: `barbers`
*   `id`, `barber_shop_id`, `user_id` (Nullable), `name`, `phone`, `photo`, `bio`, `active`.

#### Tabela: `services`
*   `id`, `barber_shop_id`, `name`, `description`, `price` (Decimal 12,2), `duration_minutes` (Int), `active`.

#### Tabela: `working_hours`
*   `id`, `barber_shop_id`, `day_of_week` (Enum: Monday...Sunday), `open_time` (Time), `close_time` (Time), `is_closed` (Bool).

#### Tabela: `customers`
*   `id`, `barber_shop_id`, `name`, `phone`, `email`, `photo`, `total_bookings` (Int).

---

### 🔴 Grupi 3: Rezervimet & SMS (The Engine)
#### Tabela: `bookings`
*   `id`, `barber_shop_id`, `barber_id`, `service_id`, `customer_id`, `appointment_at` (DateTime), `status` (pending, confirmed, completed, cancelled, no-show), `total_price`, `notes`.

#### Tabela: `sms_queue` (Radha e dërgimit)
*   `id`, `barber_shop_id`, `booking_id`, `phone_number`, `message_content`, `scheduled_at` (Kur duhet të dërgohet), `status` (pending, processing, sent, failed), `retry_count`.

#### Tabela: `sms_templates` (Personalizimi i mesazheve)
*   `id`, `barber_shop_id`, `type` (reminder, confirmation, welcome), `content` (Përmban placeholder si {customer_name}, {time}).

---

## 🔄 3. Detajimi i Komunikimit (The "How it Works")

### A. Rasti: Krijimi i një Rezervimi
1.  **Veprimi:** Klienti ose Berberi krijon një `booking`.
2.  **Serveri:** Regjistron të dhënat dhe gjeneron një `Event` (BookingChanged).
3.  **Reverb:** Shpërndan eventin te të gjithë: APK-ja e berberit dridhet (vibrates) dhe tabela e rezervimeve përditësohet në sekondë.
4.  **Reminder Engine:** Një task në server (Cron Job) llogarit kohën: "Nëse rezervimi është pas 1 ore, krijo një rresht në `sms_queue`".

### B. Rasti: SMS Reminder (Firebase + APK Gateway)
1.  **Serveri:** Identifikon mesazhin "Pending" në `sms_queue` që duhet dërguar TANI.
2.  **Firebase (FCM):** Serveri dërgon një "Data Message" (njoftim i padukshëm) te APK-ja e pronarit/berberit.
3.  **APK (Flutter):** Në momentin që merr mesazhin nga Firebase, Flutter thërret një kod Native (Android Intent) për të dërguar SMS automatik nga karta SIM e telefonit.
4.  **Callback:** APK njofton serverin: "SMS u dërgua me sukses". Serveri përditëson `sms_queue` në `status = sent`.

---

## 🚀 4. Pse kjo është "God Mode" për Ty?
*   **Zero Kosto SMS:** Duke përdorur APK-në si Gateway, ti nuk paguan platforma si Twilio. Çdo dyqan përdor limitet e veta të telefonit.
*   **Whitelabeling i Plotë:** Ti jep vetëm 1 APK, por ajo duket ndryshe për çdo berber.
*   **Kontroll Global:** Ti mund të futesh në çdo dyqan (Bypass) për të parë pse një SMS nuk është dërguar ose si po ecin punët.
*   **Përditësim Automatik:** Kur ti ndryshon diçka në Database, APK-ja reflekton ndryshimin në çast pa pasur nevojë që berberi të mbyllë dhe hapë aplikacionin.

---

## 🛠️ 5. Lista e Teknologjive (Technical Stack)
| Komponenti | Teknologjia |
| :--- | :--- |
| **Language** | PHP 8.3 + Kotlin + Dart |
| **Framework** | Laravel 11 + Flutter 3.x |
| **Realtime** | Laravel Reverb (WebSockets) |
| **Notifications** | Firebase FCM (Push & Data) |
| **Database** | MySQL / MariaDB |
| **Scaffolding** | Universal `new:view` DDD Generator |

Ky dokumentacion përfshin çdo hallkë të sistemit të kërkuar.
