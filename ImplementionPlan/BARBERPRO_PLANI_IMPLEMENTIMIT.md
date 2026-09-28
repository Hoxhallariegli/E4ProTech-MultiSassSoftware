# 💈 BarberPro SaaS — Plani PERFEKT i Implementimit
*(new:view si bazë, modifikime manuale pas — versioni final v3)*

**Shtesa e fundit:** Firebase (push by events) dhe Reverb (sync by actions) tani menaxhohen **për çdo `barber_shop_id` veç e veç**, jo globalisht — një shop mund ta fikë push-in për një event specifik (p.sh. "payments.updated") pa prekur shopet e tjerë. Templates ishin tashmë me `barber_shop_id` që nga versioni i mëparshëm — konfirmuar më poshtë.

---

## 0. Vendimet (finale, të gjitha)

| Pyetje | Vendimi |
|---|---|
| Anulimi | Pa limit orësh |
| Kanalet e njoftimit (SMS/WhatsApp) | `notification_channels` — on/off + limit ditor, **per barber_shop_id** |
| Firebase push | On/off **per event, per barber_shop_id** (jo global) — tabelë e re `event_settings` |
| Reverb sync | On/off **per event/action, per barber_shop_id** — e njëjta tabelë `event_settings` |
| Template-t | `message_templates` — kishte tashmë `barber_shop_id`, konfirmuar ✅ |
| Payment | cash/card tani, Stripe më vonë |
| Overlap rezervimesh | I ndaluar gjithmonë |
| Klienti | Faqe publike Livewire, jo app e veçantë |

---

## 1. Rregulla transversale

- Multi-tenancy: `barber_shop_id` foreignId jo-nullable në çdo tabelë biznesi (përveç `plans`).
- `is_/has_/active/enabled` → boolean automatik; `_at` → datetime automatik; `_id` → foreignId automatik.
- Pas çdo moduli: kontrollo `label`+`module` te `permissions`, shto hyrjen te `channels.php`, shto rresht te `realtime_events`.
- **E RE**: çdo event i ri i shtuar te `realtime_events` DUHET të gjenerojë automatikisht një rresht `event_settings` për çdo shop ekzistues (Observer, shih §3).

---

## 2. Modulet, fushat dhe KOMANDAT (radhë sipas varësisë)

### 1) `BarberShop`
```bash
php artisan new:view BarberShop
```
`owner_id` (foreignId→users) · `name` · `app_name` · `slug` (shto `->unique()` manualisht) · `logo` (image) · `banner` (image) · `primary_color` · `secondary_color` · `trial_ends_at` (datetime, nullable) · `active` (boolean) · `timezone` (string) · `max_no_show_before_block` (integer, nullable)

### 2) *(manual)* — shto `barber_shop_id` te `users`
```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('barber_shop_id')->nullable()->constrained()->nullOnDelete();
});
```

### 3) `Plan`
```bash
php artisan new:view Plan
```
`name` · `price` (price) · `duration_months` (integer) · `max_barbers` (integer) · `max_services` (integer) · `active` (boolean)

### 4) `Subscription`
```bash
php artisan new:view Subscription
```
`barber_shop_id` · `plan_id` · `starts_at` (date) · `ends_at` (date) · `status` (enum: trial,active,expired,cancelled) · `auto_renew` (boolean)

### 5) `Barber`
```bash
php artisan new:view Barber
```
`barber_shop_id` · `user_id` (nullable manualisht) · `name` · `phone` · `photo` · `bio` (text) · `active` (boolean)

### 6) `Service`
```bash
php artisan new:view Service
```
`barber_shop_id` · `name` · `description` (text) · `price` (price) · `duration_minutes` (integer) · `category` (string, nullable) · `active` (boolean)

### 7) `WorkingHour`
```bash
php artisan new:view WorkingHour
```
`barber_shop_id` · `day_of_week` (enum) · `open_time` · `close_time` · `is_closed` (boolean)
⚠️ ndrysho `open_time`/`close_time` → `->time()` manualisht.

### 8) `Customer`
```bash
php artisan new:view Customer
```
`barber_shop_id` · `name` · `phone` · `email` (nullable) · `photo` (nullable) · `total_bookings` (integer, read-only) · `no_show_count` (integer, read-only) · `blocked_at` (datetime, nullable)

### 9) `NotificationChannel` — SMS/WhatsApp on-off per shop
```bash
php artisan new:view NotificationChannel --api
```
`barber_shop_id` · `channel` (enum: sms,whatsapp) · `enabled` (boolean, default false) · `daily_limit` (integer, nullable)

### 10) `EventSetting` — **E RE**: Firebase + Reverb on/off PËR ÇDO EVENT, PËR ÇDO SHOP
```bash
php artisan new:view EventSetting --api
```
`barber_shop_id` (foreignId→barber_shops) · `realtime_event_id` (foreignId→realtime_events) · `reverb_enabled` (boolean, default true) · `firebase_enabled` (boolean, default true)

⚠️ Manuale pas gjenerimit:
- Shto unique constraint composite: `$table->unique(['barber_shop_id', 'realtime_event_id']);`
- **Observer te `RealtimeEvent::created`**: kur krijohet event i ri global (p.sh. gjatë `new:view` të një moduli të ardhshëm), krijo automatikisht një rresht `EventSetting` për ÇDO `barber_shop` ekzistues (me default `reverb_enabled=true`, `firebase_enabled = realtime_events.firebase_enabled` fillestar).
- **Observer te `BarberShop::created`**: kur krijohet shop i ri, krijo automatikisht një rresht `EventSetting` për ÇDO `realtime_event` ekzistues (default enabled).
- **Në kodin që bën broadcast** (Reverb) dhe **në kodin që bën push** (Firebase), para se me dërgu, kontrollo `EventSetting` për `(barber_shop_id, event)` — nëse `reverb_enabled=false`, mos e transmeto; nëse `firebase_enabled=false`, mos dërgo push, edhe pse eventi ndodhi.
- **UI Web** (Livewire, Settings → Njoftimet e sistemit): matricë me rreshta = eventet (nga `realtime_events`), kolona = Reverb toggle | Firebase toggle — specifike për shopin që je duke parë (ose global për Super Admin nëse sheh "të gjitha shopet").
- **API** (i njëjti endpoint) — APK mund të lexojë/ndryshojë të njëjtën matricë nga celulari i pronarit.

### 11) `MessageTemplate`
```bash
php artisan new:view MessageTemplate
```
`barber_shop_id` · `channel` (enum: sms,whatsapp) · `type` (enum: reminder,confirmation,welcome) · `content` (text)
✅ Konfirmuar: `barber_shop_id` është këtu that që nga fillimi — çdo shop ka template-t e veta, të menaxhueshme nga Web dhe API/APK identikisht.

### 12) `Booking` (MOTORI KRYESOR)
```bash
php artisan new:view Booking
```
`barber_shop_id` · `barber_id` · `service_id` · `customer_id` · `appointment_at` (datetime) · `status` (enum: pending,confirmed,completed,cancelled,no-show) · `total_price` (price) · `notes` (text, nullable) · `source` (enum: online,walk-in,phone)

⚠️ Logjikë manuale:
1. **Overlap**: kontrollo `[appointment_at, +duration_minutes]` s'përplaset për të njëjtin `barber_id`.
2. **No-show**: `status=no-show` → rrit `customers.no_show_count`; nëse arrin limit → `blocked_at=now()`.
3. **`total_bookings`**: rritet automatikisht (observer).
4. **Mesazh automatik**: krijo rresht `message_queue` për çdo kanal `enabled` te `notification_channels`, tekst nga `message_templates`.
5. **Broadcast/Push**: çdo ndryshim statusi → kontrollo `EventSetting` (Hapi 10) para se me transmetu në Reverb ose push Firebase.

### 13) `Payment`
```bash
php artisan new:view Payment
```
`barber_shop_id` · `booking_id` · `amount` (price) · `method` (enum: cash,card) · `status` (enum: pending,paid,refunded,failed)

### 14) `MessageQueue`
```bash
php artisan new:view MessageQueue
```
`barber_shop_id` · `booking_id` · `channel` (enum: sms,whatsapp) · `phone_number` · `message_content` (text) · `scheduled_at` (datetime) · `status` (enum: pending,processing,sent,failed,skipped_limit) · `retry_count` (integer)

### 15) `MessageLog`
```bash
php artisan new:view MessageLog
```
`barber_shop_id` · `customer_id` · `channel` (enum: sms,whatsapp) · `message` (text) · `status` (enum: sent,failed) · `sent_at` (datetime, nullable)

### 16) `DeviceToken`
```bash
php artisan new:view DeviceToken --firebase
```
`barber_shop_id` · `user_id` · `fcm_token` (string) · `platform` (enum: android,ios) · `last_used_at` (datetime, nullable)

### 17) `Review`
```bash
php artisan new:view Review
```
`barber_shop_id` · `barber_id` · `customer_id` · `booking_id` · `rating` (integer 1-5) · `comment` (text, nullable)

---

## 3. Të gjitha komandat, radhazi

```bash
php artisan new:view BarberShop
# --- manual: migrimi barber_shop_id te users ---

php artisan new:view Plan
php artisan new:view Subscription
php artisan new:view Barber
php artisan new:view Service
php artisan new:view WorkingHour
php artisan new:view Customer
php artisan new:view NotificationChannel --api
php artisan new:view EventSetting --api
php artisan new:view MessageTemplate
php artisan new:view Booking
php artisan new:view Payment
php artisan new:view MessageQueue
php artisan new:view MessageLog
php artisan new:view DeviceToken --firebase
php artisan new:view Review

php artisan migrate
```

---

## 4. Menaxhimi për ty si Super Admin

1. **Dashboard metrikash**: tenants aktivë/trial/skaduar, mesazhe dërguar/dështuar sot, revenue mujor.
2. **`CheckSubscriptionExpirations`** (ditor): skadon → `expired` + `active=false`; brenda 3 ditësh → njoftim.
3. **`CheckMessageFailures`** (çdo orë): alarm kur dështon rëndë.
4. **Backup**: `spatie/laravel-backup`, ditor.
5. **E RE — Paneli i eventeve** (nga §2, Hapi 10): Super Admin sheh/redakton matricën Reverb+Firebase për çdo shop, ose vendos default global për shope të reja.

---

## 5. Faqja publike e rezervimit (klienti)

`domain.com/{shop_slug}/rezervo` — zgjedh shërbim → berber (ose "cilido") → sheh slot-e (nga `working_hours` minus `bookings`) → emër+telefon → `customer`+`booking` (`source=online`) → nëse `blocked_at` s'është null, refuzohet. Pas `completed` → mesazh me link `Review`.

---

## 6. Operacionale (para launch)

- **Timezone**: `barber_shop.timezone` në display + planifikim mesazhesh.
- **Rate-limit**: `notification_channels.daily_limit`.
- **Reverb/Firebase granular**: `event_settings` (Hapi 10) — asnjë shop s'merr njoftim/sync që s'e ka aktivizuar vetë.
- **Testim** (Pest): overlap · expirations job · message queue (limit+retry) · no-show block · **EventSetting respektohet para broadcast/push**.
- **Backup**: §4.4.

---

Gati për të filluar me `php artisan new:view BarberShop`?
