# =========================================================================
# BarberPro SaaS — Komandat e plota new:view (piped input, radhë e detyrueshme)
# =========================================================================
# FORMATI: secili element i array-it është një përgjigje për prompt-in tjetër
#   - fusha e zakonshme (string/text/integer/decimal/boolean/image/date/datetime):
#         "emri_fushes", "<type_index>", "<y|n nullable>"
#   - fushë enum:
#         "emri_fushes", "12", "opsion1, opsion2, ...", "<y|n>"
#   - fushë foreignId:
#         "emri_fushes", "11", "<tabela_e_lidhur>", "<fusha_per_shfaqje>", "<y|n>"
#   - "" (string bosh) mbyll listën e fushave
#   - pastaj: "<icon>" për "Menu icon"
#   - "Generate API?" NUK pyetet fare nëse --api është dhënë si flag (short-circuit) —
#     prandaj s'ka nevojë për "Yes" në fund kur përdorim --api.
#
# TYPE INDEX (nga menuja e new:view, e pandryshuar):
#   0 string   1 text   2 integer   3 bigInteger   4 decimal   5 qty
#   6 price    7 boolean 8 image    9 date        10 datetime  11 foreignId  12 enum
#
# ⚠️ RENDITJA ËSHTË E DETYRUESHME (foreign keys). Mos e ndrysho.
# ⚠️ Çdo komandë krijon tabelën dhe migron menjëherë — nëse dëshiron ta rigjenerosh
#    një modul, duhet të fshish tabelën përkatëse manualisht më parë (Schema::hasTable
#    e refuzon pavarësisht nga --force, sepse --force mbulon vetëm skedarët, jo DB-në).
# =========================================================================

# --- 1) BarberShop ---
@(
    "owner_id","11","users","name","n",
    "name","0","n",
    "app_name","0","n",
    "slug","0","n",
    "logo","8","y",
    "banner","8","y",
    "primary_color","0","n",
    "secondary_color","0","n",
    "trial_ends_at","10","y",
    "expires_at","10","y",
    "active","7","n",
    "sms_enabled","7","n",
    "timezone","0","n",
    "max_no_show_before_block","2","y",
    "",
    "building-office"
) | php artisan new:view BarberShop --api --force

# --- (manual, jo new:view) shto barber_shop_id te users, pastaj: ---
# php artisan make:migration add_barber_shop_id_to_users_table --table=users
# php artisan migrate

# --- 2) Plan ---
@(
    "name","0","n",
    "price","6","n",
    "duration_months","2","n",
    "max_barbers","2","n",
    "max_services","2","n",
    "max_shops","2","n",
    "active","7","n",
    "",
    "chart-bar"
) | php artisan new:view Plan --api --force

# --- 3) Subscription ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "plan_id","11","plans","name","n",
    "starts_at","10","n",
    "ends_at","10","n",
    "status","12","trial, active, expired, cancelled","n",
    "auto_renew","7","n",
    "",
    "clipboard"
) | php artisan new:view Subscription --api --force

# --- 4) Barber ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "user_id","11","users","name","y",
    "name","0","n",
    "phone","0","n",
    "photo","8","y",
    "bio","1","y",
    "active","7","n",
    "",
    "user"
) | php artisan new:view Barber --api --force

# --- 5) Service ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "name","0","n",
    "description","1","y",
    "price","6","n",
    "duration_minutes","2","n",
    "category","0","y",
    "active","7","n",
    "",
    "pencil-square"
) | php artisan new:view Service --api --force

# --- 6) WorkingHour ---
# ⚠️ pas gjenerimit: ndrysho manualisht open_time/close_time nga datetime -> ->time()
@(
    "barber_shop_id","11","barber_shops","name","n",
    "day_of_week","12","Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday","n",
    "open_time","10","n",
    "close_time","10","n",
    "is_closed","7","n",
    "",
    "clipboard"
) | php artisan new:view WorkingHour --api --force

# --- 7) Customer ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "name","0","n",
    "phone","0","n",
    "email","0","y",
    "photo","8","y",
    "total_bookings","2","n",
    "no_show_count","2","n",
    "blocked_at","10","y",
    "",
    "users"
) | php artisan new:view Customer --api --force

# --- 8) NotificationChannel ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "channel","12","sms, whatsapp","n",
    "enabled","7","n",
    "daily_limit","2","y",
    "",
    "bell"
) | php artisan new:view NotificationChannel --api --force

# --- 9) EventSetting ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "realtime_event_id","11","realtime_events","event","n",
    "reverb_enabled","7","n",
    "firebase_enabled","7","n",
    "",
    "bolt"
) | php artisan new:view EventSetting --api --force

# --- 10) MessageTemplate ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "channel","12","sms, whatsapp","n",
    "type","12","reminder, confirmation, welcome","n",
    "content","1","n",
    "",
    "document"
) | php artisan new:view MessageTemplate --api --force

# --- 11) Booking (MOTORI KRYESOR) ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "barber_id","11","barbers","name","n",
    "service_id","11","services","name","n",
    "customer_id","11","customers","name","n",
    "appointment_at","10","n",
    "status","12","pending, confirmed, completed, cancelled, no-show","n",
    "total_price","6","n",
    "notes","1","y",
    "source","12","online, walk-in, phone","n",
    "",
    "chart-bar"
) | php artisan new:view Booking --api --force

# --- 12) Payment ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "booking_id","11","bookings","customer.name","n",
    "amount","6","n",
    "method","12","cash, card","n",
    "status","12","pending, paid, refunded, failed","n",
    "",
    "archive-box"
) | php artisan new:view Payment --api --force

# --- 13) MessageQueue ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "booking_id","11","bookings","customer.name","n",
    "channel","12","sms, whatsapp","n",
    "phone_number","0","n",
    "message_content","1","n",
    "scheduled_at","10","n",
    "status","12","pending, processing, sent, failed, skipped_limit","n",
    "retry_count","2","n",
    "",
    "bell"
) | php artisan new:view MessageQueue --api --force

# --- 14) MessageLog ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "customer_id","11","customers","name","n",
    "channel","12","sms, whatsapp","n",
    "message","1","n",
    "status","12","sent, failed","n",
    "sent_at","10","y",
    "",
    "document"
) | php artisan new:view MessageLog --api --force

# --- 15) DeviceToken (--firebase shtohet vetëm këtu) ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "user_id","11","users","name","n",
    "fcm_token","0","n",
    "platform","12","android, ios","n",
    "last_used_at","10","y",
    "",
    "bolt"
) | php artisan new:view DeviceToken --api --firebase --force

# --- 16) Review ---
@(
    "barber_shop_id","11","barber_shops","name","n",
    "barber_id","11","barbers","name","n",
    "customer_id","11","customers","name","n",
    "booking_id","11","bookings","customer.name","n",
    "rating","2","n",
    "comment","1","y",
    "",
    "pencil-square"
) | php artisan new:view Review --api --force

# =========================================================================
# PAS GJITHË KËSAJ (manuale, gjithmonë):
#   1. Migrimi barber_shop_id -> users (menjëherë pas BarberShop, siç u tha lart)
#   2. slug ->unique() te migration e BarberShop
#   3. total_bookings/no_show_count default(0) te migration e Customer
#   4. open_time/close_time -> ->time() te migration e WorkingHour
#   5. unique(['barber_shop_id','realtime_event_id']) te migration e EventSetting
#   6. Observer-at: RealtimeEvent::created / BarberShop::created -> EventSetting
#   7. Logjika e Booking: overlap, no-show counter, sms_queue auto-create
#   8. ProcessMessageQueue command (rate-limit + retry + notifications)
#   9. CheckSubscriptionExpirations + CheckMessageFailures commands
#  10. Faqja publike e rezervimit ({shop_slug}/rezervo)
# =========================================================================
