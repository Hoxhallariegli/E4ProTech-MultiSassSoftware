# 💈 BarberPro SaaS - Master Blueprint (Web & APK)

Ky dokument shërben si udhëzuesi kryesor teknik dhe operacional për të gjithë sistemin. Ky është një sistem **Multi-Tenant SaaS** (Software as a Service) ku një instalim i vetëm menaxhon qindra biznese të pavarura.

---

## 🎯 1. Vizioni i Projektit
Krijimi i një platforme ku çdo Berber/Sallon mund të regjistrohet, të marrë një APK menaxhimi, dhe të automatizojë të gjithë biznesin e tij (Rezervimet, SMS, Stafi, Pagesat).

*   **Për Ty (Super Admin):** Menaxhim total, vjelje e abonimeve, kontroll trial-esh.
*   **Për Biznesin (Tenant):** Një aplikacion "Whitelabel" që duket si i tyre (Logo/Ngjyra) dhe menaxhon çdo detaj.
*   **Për Klientin:** Eksperiencë e lehtë rezervimi dhe njoftime automatike.

---

## 🏗️ 2. Arkitektura Teknike (The Stack)
*   **Core:** Laravel 11 (PHP 8.3) me strukturë Domain-Driven Design (DDD).
*   **Frontend Web:** Livewire 3 + Tailwind CSS (Dashboard Pro).
*   **Mobile Engine:** Flutter (BLoC/Cubit) me sinkronizim automatik API.
*   **Realtime Data:** Laravel Reverb (WebSockets) - Për përditësimin e radhës dhe rezervimeve pa refresh.
*   **Push Engine:** Firebase FCM - Për njoftimet në background.
*   **SMS Engine:** Android Gateway Integration (Dërgimi i SMS direkt nga celulari i berberit).

---

## 🗄️ 3. Struktura e Plotë e Database (Database Schema)

### A. Sistemi i Tenancës (SaaS Core)
| Tabela | Fusha Kryesore | Funksioni |
| :--- | :--- | :--- |
| `barber_shops` | `name`, `slug`, `branding_json`, `logo`, `banner`, `sms_enabled`, `trial_ends_at`, `owner_id`, `active` | Tabela qëndrore e çdo biznesi. |
| `plans` | `name`, `price`, `duration_months`, `max_barbers`, `max_services`, `active` | Paketat që ti shet. |
| `subscriptions` | `barber_shop_id`, `plan_id`, `starts_at`, `ends_at`, `status` | Lidhja e biznesit me pagesën. |

### B. Menaxhimi i Biznesit (Business Logic)
| Tabela | Fusha Kryesore | Funksioni |
| :--- | :--- | :--- |
| `users` | `name`, `email`, `password`, `role`, `barber_shop_id` | Përdoruesit (Admin, Pronar, Berber, Klient). |
| `barbers` | `barber_shop_id`, `user_id`, `name`, `photo`, `bio`, `active` | Stafi i sallonit. |
| `services` | `barber_shop_id`, `name`, `price`, `duration`, `category` | Çfarë ofron salloni. |
| `working_hours` | `barber_shop_id`, `day_of_week`, `open_time`, `close_time`, `is_closed` | Planet orare. |

### C. Operacionet (Daily Workflow)
| Tabela | Fusha Kryesore | Funksioni |
| :--- | :--- | :--- |
| `customers` | `barber_shop_id`, `user_id`, `name`, `phone`, `email` | Baza e të dhënave të klientëve. |
| `bookings` | `barber_shop_id`, `barber_id`, `customer_id`, `service_id`, `appointment_at`, `status` (pending, confirmed, completed, cancelled, no-show) | Motori i rezervimeve. |
| `payments` | `barber_shop_id`, `booking_id`, `amount`, `method`, `status` | Regjistrimi i arkës. |
| `sms_logs` | `barber_shop_id`, `customer_id`, `message`, `status`, `sent_at` | Monitorimi i njoftimeve. |

---

## 🔐 4. Logjika e Sigurisë dhe Multi-Tenancy
*   **Tenant Isolation:** Të gjitha tabelat (përveç `plans`) kanë `barber_shop_id`.
*   **Global Scope:** Laravel filtron automatikisht çdo Query: `SELECT * FROM bookings WHERE barber_shop_id = ?`.
*   **Super Admin Bypass:** Ti si Admin ke rolin që anashkalon këtë filtër për të parë gjithçka.
*   **APK Whitelabeling:** Kur një përdorues logohet në APK, aplikacioni shkarkon `branding_json` dhe ndryshon `PrimaryColor` dhe `Logo` në kohë reale.

---

## 📱 5. Funksionaliteti i APK (Management Tool)
APK-ja nuk është vetëm një pasqyrë, është një **stacion pune**:
1.  **Dashboard-i i Berberit:** Sheh radhën e ditës, klikon "Next" për të thirrur klientin tjetër.
2.  **Krijimi i Rezervimeve:** Nëse vjen një klient pa rezervim, berberi e shton direkt nga celulari.
3.  **SMS Gateway:** Nëse `sms_enabled` është `true`, APK dërgon automatikisht një SMS te klienti 1 orë para rezervimit duke përdorur kartën SIM të telefonit (ose API gateway).
4.  **Realtime Alerts:** Sapo vjen një rezervim i ri nga Web ose një klient tjetër, celulari i berberit njofton menjëherë përmes Reverb.

---

## 🚀 6. Plani i Implementimit (RMM - Roadmap)

### Faza 1: Fondacioni (Done ✅)
- [x] Instalimi i Laravel 11 + Livewire 3 + Reverb.
- [x] Krijimi i `new:view` motorit profesional.
- [x] Implementimi i Multi-Tenancy (BelongsToBarberShop Trait).
- [x] Gjenerimi i moduleve bazë (Barber, Service, Booking, etj).

### Faza 2: APK & Sync (Në Proces 🔄)
- [ ] Lidhja e Flutter me API-të e gjeneruara.
- [ ] Implementimi i Realtime sinkronizimit (Reverb në Mobile).
- [ ] Konfigurimi i Firebase për Push Notifications.

### Faza 3: Sistemi SaaS & Pagesat (E ardhshmja 📅)
- [ ] Ndërtimi i Dashboard-it të Super Adminit.
- [ ] Integrimi i Pagesave (Stripe ose Cash management) për abonimet.
- [ ] Automatikisht fikja e aksesit nëse abonimi skadon.

### Faza 4: Automatizimi & Marketing
- [ ] Sistemi i Reminder-ave automatikë (Cron Jobs).
- [ ] Raportet e fitimeve dhe performancës për çdo sallon.

---

**SHËNIM:** Çdo modul i ri që do të na duhet, do të krijohet me `php artisan new:view` për të ruajtur standardin e lartë të kodit dhe sinkronizimin me APK.
