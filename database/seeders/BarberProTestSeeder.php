<?php

namespace Database\Seeders;

use App\Models\BarberShop;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Barber;
use App\Models\Service;
use App\Models\WorkingHour;
use App\Models\Customer;
use App\Models\NotificationChannel;
use App\Models\RealtimeEvent;
use App\Models\EventSetting;
use App\Models\MessageTemplate;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\MessageQueue;
use App\Models\MessageLog;
use App\Models\DeviceToken;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * Seeder testimi — krijon një skenar të plotë (1 barber shop, staf, shërbime,
 * orare, klientë, rezervime në statuse të ndryshme, mesazhe, review) për të
 * verifikuar shpejt që çdo modul dhe çdo relacion funksionon si duhet.
 *
 * Run: php artisan db:seed --class=BarberProTestSeeder
 */
class BarberProTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Owner + BarberShop
        $owner = User::firstOrCreate(
            ['email' => 'barber1@test.com'],
            [
                'name' => 'Ardit Berberi',
                'slug' => 'ardit-berberi',
                'password' => Hash::make('password'),
                'is_office_login_only' => false,
                'is_active' => true,
            ]
        );

        $shop = BarberShop::create([
            'owner_id' => $owner->id,
            'name' => 'BERBERANA 1',
            'app_name' => 'BAR1',
            'slug' => 'berberana-1',
            'logo' => 'placeholder.png',
            'banner' => 'placeholder.png',
            'primary_color' => '#111111',
            'secondary_color' => '#f5a623',
            'trial_ends_at' => now()->addDays(14),
            'expires_at' => now()->addYear(),
            'sms_enabled' => true,
            'active' => true,
            'timezone' => 'Europe/Tirane',
            'max_no_show_before_block' => 3,
        ]);

        $shop2 = BarberShop::create([
            'owner_id' => $owner->id,
            'name' => 'BERBERANA 2',
            'app_name' => 'BAR2',
            'slug' => 'berberana-2',
            'logo' => 'placeholder.png',
            'banner' => 'placeholder.png',
            'primary_color' => '#222222',
            'secondary_color' => '#e44d26',
            'trial_ends_at' => now()->addDays(14),
            'expires_at' => now()->addYear(),
            'sms_enabled' => false,
            'active' => true,
            'timezone' => 'Europe/Tirane',
            'max_no_show_before_block' => 5,
        ]);

        // link owner to the shop and assign QQQ role for these teams
        $owner->update(['barber_shop_id' => $shop->id]);
        $owner->barberShops()->sync([$shop->id, $shop2->id]);

        $roleQQQ = \App\Models\Role::firstOrCreate(['name' => 'QQQ'], ['label' => 'QQQ Role']);
        $allPermissions = \App\Models\Permission::all();
        $roleQQQ->syncPermissions($allPermissions);

        // Assign QQQ role to Ardit for both shops using Spatie Teams (IDEMPOTENT)
        $roleQQQ = \App\Models\Role::where('name', 'QQQ')->first();
        if ($roleQQQ) {
            foreach ([$shop->id, $shop2->id] as $sid) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $roleQQQ->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $owner->id,
                        'barber_shop_id' => $sid
                    ],
                    ['barber_shop_id' => $sid]
                );
            }
        }

        // Reset team id for global context
        setPermissionsTeamId(null);

        // 2. Plans
        $planStarter = Plan::create([
            'name' => 'Starter',
            'price' => 19.99,
            'duration_months' => 1,
            'max_barbers' => 3,
            'max_services' => 10,
            'max_shops' => 1,
            'active' => true,
        ]);

        $planPro = Plan::create([
            'name' => 'Pro',
            'price' => 49.99,
            'duration_months' => 1,
            'max_barbers' => 10,
            'max_services' => 50,
            'max_shops' => 3,
            'active' => true,
        ]);

        $planUnlimited = Plan::create([
            'name' => 'Unlimited',
            'price' => 99.99,
            'duration_months' => 1,
            'max_barbers' => 999,
            'max_services' => 999,
            'max_shops' => 999,
            'active' => true,
        ]);

        // Subscription for Shop 1 (Pro)
        Subscription::create([
            'barber_shop_id' => $shop->id,
            'plan_id' => $planPro->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'auto_renew' => true,
        ]);

        // Subscription for Shop 2 (Starter)
        Subscription::create([
            'barber_shop_id' => $shop2->id,
            'plan_id' => $planStarter->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'auto_renew' => true,
        ]);

        // 3. Barbers
        $barberUser = User::firstOrCreate(
            ['email' => 'barber1@test.com'],
            [
                'name' => 'Ardit Berberi',
                'slug' => 'ardit-berberi',
                'password' => Hash::make('password'),
                'is_office_login_only' => false,
                'is_active' => true,
                'barber_shop_id' => $shop->id,
            ]
        );

        $barber1 = Barber::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $barberUser->id,
            'name' => 'Ardit Berberi',
            'phone' => '+355691111111',
            'photo' => 'barber1.png',
            'bio' => '10 vjet eksperiencë.',
            'active' => true,
        ]);

        $barberUser2 = User::firstOrCreate(
            ['email' => 'barber2@test.com'],
            [
                'name' => 'Elton Berberi',
                'slug' => 'elton-berberi',
                'password' => Hash::make('password'),
                'is_office_login_only' => false,
                'is_active' => true,
                'barber_shop_id' => $shop->id,
            ]
        );

        $barber2 = Barber::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $barberUser2->id,
            'name' => 'Elton Berberi',
            'phone' => '+355692222222',
            'photo' => 'barber2.png',
            'bio' => 'Specialist në flokë kaçurrela.',
            'active' => true,
        ]);

        // 4. Services
        $haircut = Service::create([
            'barber_shop_id' => $shop->id,
            'name' => 'Prerje flokësh',
            'description' => 'Prerje standarde.',
            'price' => 10.00,
            'duration_minutes' => 30,
            'category' => 'flokë',
            'active' => true,
        ]);

        $beard = Service::create([
            'barber_shop_id' => $shop->id,
            'name' => 'Rregullim mjekre',
            'description' => 'Rregullim + trim.',
            'price' => 7.00,
            'duration_minutes' => 20,
            'category' => 'mjekër',
            'active' => true,
        ]);

        // 5. Working hours (Mon-Fri 09:00-19:00, Sat 09:00-14:00, Sun closed)
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($days as $day) {
            WorkingHour::create([
                'barber_shop_id' => $shop->id,
                'day_of_week' => $day,
                'open_time' => '09:00',
                'close_time' => '19:00',
                'is_closed' => false,
            ]);
        }
        WorkingHour::create([
            'barber_shop_id' => $shop->id, 'day_of_week' => 'Saturday',
            'open_time' => '09:00', 'close_time' => '14:00', 'is_closed' => false,
        ]);
        WorkingHour::create([
            'barber_shop_id' => $shop->id, 'day_of_week' => 'Sunday',
            'open_time' => '00:00', 'close_time' => '00:00', 'is_closed' => true,
        ]);

        // 6. Customers
        $customer1 = Customer::create([
            'barber_shop_id' => $shop->id, 'name' => 'Gentian Klienti',
            'phone' => '+355693333333', 'email' => 'gentian@test.com',
            'photo' => 'cust1.png',
            'total_bookings' => 0, 'no_show_count' => 0,
        ]);
        $customer2 = Customer::create([
            'barber_shop_id' => $shop->id, 'name' => 'Sara Kliente',
            'phone' => '+355694444444', 'email' => 'sara@test.com',
            'photo' => 'cust2.png',
            'total_bookings' => 0, 'no_show_count' => 0,
        ]);

        // 7. Notification channels
        NotificationChannel::create([
            'barber_shop_id' => $shop->id, 'channel' => 'sms',
            'enabled' => true, 'daily_limit' => 100,
        ]);
        NotificationChannel::create([
            'barber_shop_id' => $shop->id, 'channel' => 'whatsapp',
            'enabled' => false, 'daily_limit' => null,
        ]);

        // 8. Event settings — një rresht për çdo realtime_event ekzistues
        foreach (RealtimeEvent::all() as $event) {
            EventSetting::firstOrCreate(
                ['barber_shop_id' => $shop->id, 'realtime_event_id' => $event->id],
                ['reverb_enabled' => true, 'firebase_enabled' => $event->firebase_enabled ?? true]
            );
        }

        // 9. Message templates
        MessageTemplate::create([
            'barber_shop_id' => $shop->id, 'channel' => 'sms', 'type' => 'reminder',
            'content' => 'Përshëndetje {customer_name}, ju kujtojmë rezervimin tuaj në orën {time}.',
        ]);
        MessageTemplate::create([
            'barber_shop_id' => $shop->id, 'channel' => 'sms', 'type' => 'confirmation',
            'content' => 'Rezervimi juaj për {time} u konfirmua. Faleminderit {customer_name}!',
        ]);
        MessageTemplate::create([
            'barber_shop_id' => $shop->id, 'channel' => 'sms', 'type' => 'welcome',
            'content' => 'Mirë se erdhe {customer_name}! Faleminderit që zgjodhe sallonin tonë.',
        ]);

        // 10. Bookings (statuse të ndryshme, për të testuar çdo rrugë)
        $bookingConfirmed = Booking::create([
            'barber_shop_id' => $shop->id, 'barber_id' => $barber1->id,
            'service_id' => $haircut->id, 'customer_id' => $customer1->id,
            'appointment_at' => now()->addDay()->setTime(10, 0),
            'status' => 'confirmed', 'total_price' => 10.00,
            'notes' => null, 'source' => 'online',
        ]);

        $bookingCompleted = Booking::create([
            'barber_shop_id' => $shop->id, 'barber_id' => $barber2->id,
            'service_id' => $beard->id, 'customer_id' => $customer2->id,
            'appointment_at' => now()->subDay()->setTime(15, 0),
            'status' => 'completed', 'total_price' => 7.00,
            'notes' => 'Klient i rregullt.', 'source' => 'walk-in',
        ]);

        Booking::create([
            'barber_shop_id' => $shop->id, 'barber_id' => $barber1->id,
            'service_id' => $haircut->id, 'customer_id' => $customer1->id,
            'appointment_at' => now()->subDays(3)->setTime(11, 0),
            'status' => 'no-show', 'total_price' => 10.00,
            'notes' => null, 'source' => 'online',
        ]);

        // reflekto manualisht efektet që normalisht i bën Observer-i (§ Booking logic)
        $customer1->increment('total_bookings', 2);
        $customer1->increment('no_show_count', 1);
        $customer2->increment('total_bookings', 1);

        // 11. Payment (për booking-un e përfunduar)
        Payment::create([
            'barber_shop_id' => $shop->id, 'booking_id' => $bookingCompleted->id,
            'amount' => 7.00, 'method' => 'cash', 'status' => 'paid',
        ]);

        // 12. Message queue (reminder për booking-un e konfirmuar, 1 orë përpara)
        MessageQueue::create([
            'barber_shop_id' => $shop->id, 'booking_id' => $bookingConfirmed->id,
            'channel' => 'sms', 'phone_number' => $customer1->phone,
            'message_content' => 'Përshëndetje Gentian, ju kujtojmë rezervimin tuaj nesër në 10:00.',
            'scheduled_at' => $bookingConfirmed->appointment_at->subHour(),
            'status' => 'pending', 'retry_count' => 0,
        ]);

        // 13. Message log (histori e një mesazhi të dërguar tashmë)
        MessageLog::create([
            'barber_shop_id' => $shop->id, 'customer_id' => $customer2->id,
            'channel' => 'sms', 'message' => 'Rezervimi juaj u konfirmua.',
            'status' => 'sent', 'sent_at' => now()->subDay(),
        ]);

        // 14. Device token (simulim regjistrimi FCM nga APK)
        DeviceToken::create([
            'barber_shop_id' => $shop->id, 'user_id' => $barberUser->id,
            'fcm_token' => 'test-fcm-token-' . uniqid(),
            'platform' => 'android', 'last_used_at' => now(),
        ]);

        // 15. Review (për booking-un e përfunduar)
        Review::create([
            'barber_shop_id' => $shop->id, 'barber_id' => $barber2->id,
            'customer_id' => $customer2->id, 'booking_id' => $bookingCompleted->id,
            'rating' => 5, 'comment' => 'Shërbim shumë profesional!',
        ]);

        $this->command->info('✅ BarberPro test data u krijua: 1 shop, 2 barberë, 2 shërbime, 7 orare, 2 klientë, 3 rezervime, 1 pagesë, 1 mesazh në radhë, 1 log, 1 device token, 1 review.');
    }
}
