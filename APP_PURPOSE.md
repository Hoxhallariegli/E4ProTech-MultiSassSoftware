# Barber Pro - Multi-Tenant Ecosystem (SaaS)

## 🎯 Qëllimi i Projektit (Project Goal)
Ky projekt synon të krijojë një platformë gjithëpërfshirëse (Web Dashboard + Android APK) për menaxhimin e salloneve të bukurisë dhe berberëve në shkallë të gjerë (SaaS - Software as a Service).

Ideja kryesore është: **Një kod kryesor, Një APK, por Shumë Biznese.**

### 👤 Aktoret dhe Rolet
1.  **Super Admin (Ti):**
    *   Kontrollon të gjithë platformën.
    *   Krijon "Tenantët" (Barber Shops) e rinj.
    *   Menaxhon Planet e Abonimit (Subscription Plans).
    *   Përcakton kohëzgjatjen e provës (Trial) për çdo përdorues.
    *   Ka akses të plotë (Bypass) në çdo të dhënë për mbështetje teknike.

2.  **Pronari i Barber Shop (Tenant):**
    *   Menaxhon biznesin e tij brenda APK-së dhe Web-it.
    *   Krijon berberët, shërbimet dhe stafin e tij.
    *   Personalizon pamjen e APK-së (Ngjyrat, Logo, Banner).
    *   Menaxhon klientët dhe rezervimet e tij.

3.  **Klientët:**
    *   Përdorin APK-në për të bërë rezervime në dyqanin përkatës.
    *   Shohin historikun e tyre dhe marrin njoftime.

---

## 🛠️ Arkitektura Teknike
Platforma është ndërtuar me teknologjitë më moderne për të siguruar shpejtësi dhe qëndrueshmëri:
*   **Backend:** Laravel 11 me strukturë DDD (Domain Driven Design).
*   **Realtime:** Laravel Reverb (Për njoftime dhe përditësime në kohë reale pa rifreskuar faqen).
*   **Frontend Web:** Livewire 3 (Për një eksperiencë Single Page Application).
*   **Mobile:** Flutter (BLoC Architecture) me sinkronizim automatik me Backend-in.
*   **Multi-Tenancy:** Filtrimi i të dhënave bëhet përmes `barber_shop_id` në nivel database, duke siguruar që asnjë dyqan nuk sheh të dhënat e tjetrit.

---

## 📊 Struktura e Database (Fields & Logic)
Çdo modul është krijuar duke përdorur komandën profesionale `new:view`, e cila gjeneron automatikisht: **Model, Migration, Actions, API, Realtime Events, dhe Flutter BLoC.**

### 1. Moduli: BarberShop (Qendra e Tenancës)
*   `name`, `app_name`, `slug`, `address`, `phone`, `logo`, `banner`, `description`, `primary_color`, `secondary_color`, `active`, `sms_enabled`, `trial_ends_at`, `owner_id`.

### 2. Moduli: Barber (Stafi)
*   `barber_shop_id`, `user_id`, `name`, `phone`, `photo`, `bio`, `active`.

### 3. Moduli: Service (Shërbimet)
*   `barber_shop_id`, `name`, `description`, `price`, `duration_minutes`, `photo`, `active`.

### 4. Moduli: Booking (Rezervimet)
*   `barber_shop_id`, `barber_id`, `service_id`, `customer_id`, `appointment_at`, `status` (pending, confirmed, completed, cancelled), `total_price`, `notes`.

### 5. Moduli: Plan & Subscriptions (E Ardhshmja)
*   **Plan:** `name`, `price`, `duration_months`, `features`, `is_active`.
*   **Subscription:** `barber_shop_id`, `plan_id`, `starts_at`, `ends_at`, `status`.

---

## 🚀 Vizioni i Menaxhimit
Përdoruesit marrin APK-në të cilën ti e shpërndan. Kur ata logohen, APK-ja "shndërrohet" në dizajnin e tyre (Primary Color/Logo) dhe u tregon vetëm shërbimet dhe stafin e tyre. Ti si Admin, nga Dashboard-i kryesor, mund të fikësh apo ndezësh aksesin e tyre bazuar në pagesat që ata bëjnë.
