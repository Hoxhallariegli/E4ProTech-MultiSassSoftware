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
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MultiBusinessTestSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure permissions & roles exist
        $ownerRole = Role::firstOrCreate(
            ['name' => 'BarberShop Owner'],
            ['label' => 'Pronar i Biznesit / Sallonit']
        );
        $allPermissions = Permission::all();
        if ($allPermissions->isNotEmpty()) {
            $ownerRole->syncPermissions($allPermissions);
        }

        // Shared Plan
        $proPlan = Plan::firstOrCreate(
            ['name' => 'Pro Unlimited'],
            [
                'price' => 49.99,
                'duration_months' => 1,
                'max_barbers' => 20,
                'max_services' => 100,
                'max_shops' => 5,
                'active' => true,
            ]
        );

        $events = RealtimeEvent::all();

        // =========================================================================
        // BUSINESS 1: 💈 BARBERSHOP ("Gentlemen Barber Shop")
        // =========================================================================
        $owner1 = User::firstOrCreate(
            ['email' => 'owner.barber@test.com'],
            [
                'name' => 'Arian Berberi',
                'slug' => 'arian-berberi',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $shop1 = BarberShop::updateOrCreate(
            ['slug' => 'gentlemen-barber-shop'],
            [
                'owner_id' => $owner1->id,
                'name' => 'Gentlemen Barber Shop',
                'app_name' => 'Gentlemen Barber',
                'logo' => 'placeholder.png',
                'banner' => 'placeholder.png',
                'primary_color' => '#1E293B',
                'secondary_color' => '#F59E0B',
                'business_type' => 'barbershop',
                'staff_label' => 'Berber',
                'staff_label_plural' => 'Berberët',
                'shop_label' => 'Berberanë',
                'service_label' => 'Shërbimi',
                'trial_ends_at' => now()->addDays(30),
                'expires_at' => now()->addYear(),
                'sms_enabled' => true,
                'active' => true,
                'timezone' => 'Europe/Tirane',
                'max_no_show_before_block' => 3,
            ]
        );

        $owner1->update(['barber_shop_id' => $shop1->id]);
        $owner1->barberShops()->syncWithoutDetaching([$shop1->id]);
        DB::table('model_has_roles')->updateOrInsert(
            ['role_id' => $ownerRole->id, 'model_type' => User::class, 'model_id' => $owner1->id, 'barber_shop_id' => $shop1->id],
            ['barber_shop_id' => $shop1->id]
        );

        Subscription::firstOrCreate(
            ['barber_shop_id' => $shop1->id],
            ['plan_id' => $proPlan->id, 'starts_at' => now(), 'ends_at' => now()->addYear(), 'status' => 'active', 'auto_renew' => true]
        );

        $barberUser1 = User::firstOrCreate(
            ['email' => 'staff.barber@test.com'],
            ['name' => 'Mario Berberi', 'slug' => 'mario-berberi', 'password' => Hash::make('password'), 'is_active' => true, 'barber_shop_id' => $shop1->id]
        );
        $barber1 = Barber::updateOrCreate(
            ['barber_shop_id' => $shop1->id, 'user_id' => $barberUser1->id],
            ['name' => 'Mario Berberi', 'phone' => '+355691111111', 'photo' => 'barber1.png', 'bio' => 'Master Barber me 8 vite eksperiencë në prerje me stil.', 'active' => true]
        );

        $service1_1 = Service::updateOrCreate(
            ['barber_shop_id' => $shop1->id, 'name' => 'Prerje Flokësh Standarde'],
            ['description' => 'Prerje moderne, larje dhe stilim me dylli.', 'price' => 1000.00, 'duration_minutes' => 30, 'category' => 'Flokë', 'active' => true]
        );
        $service1_2 = Service::updateOrCreate(
            ['barber_shop_id' => $shop1->id, 'name' => 'Rregullim & Modelim Mjekre'],
            ['description' => 'Rroje tradicionale me brilantë dhe peshqir të ngrohtë.', 'price' => 600.00, 'duration_minutes' => 20, 'category' => 'Mjekër', 'active' => true]
        );

        $this->seedHoursAndEvents($shop1->id, $barber1->id, $events);


        // =========================================================================
        // BUSINESS 2: 💇‍♀️ BEAUTY & HAIR SALON ("Elegance Beauty Salon")
        // =========================================================================
        $owner2 = User::firstOrCreate(
            ['email' => 'owner.salon@test.com'],
            [
                'name' => 'Elena Parukieria',
                'slug' => 'elena-parukieria',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $shop2 = BarberShop::updateOrCreate(
            ['slug' => 'elegance-beauty-salon'],
            [
                'owner_id' => $owner2->id,
                'name' => 'Elegance Beauty Salon',
                'app_name' => 'Elegance Salon',
                'logo' => 'placeholder.png',
                'banner' => 'placeholder.png',
                'primary_color' => '#EC4899',
                'secondary_color' => '#8B5CF6',
                'business_type' => 'beauty_salon',
                'staff_label' => 'Parukier/e',
                'staff_label_plural' => 'Parukierët',
                'shop_label' => 'Sallon Bukurie',
                'service_label' => 'Shërbimi',
                'trial_ends_at' => now()->addDays(30),
                'expires_at' => now()->addYear(),
                'sms_enabled' => true,
                'active' => true,
                'timezone' => 'Europe/Tirane',
                'max_no_show_before_block' => 3,
            ]
        );

        $owner2->update(['barber_shop_id' => $shop2->id]);
        $owner2->barberShops()->syncWithoutDetaching([$shop2->id]);
        DB::table('model_has_roles')->updateOrInsert(
            ['role_id' => $ownerRole->id, 'model_type' => User::class, 'model_id' => $owner2->id, 'barber_shop_id' => $shop2->id],
            ['barber_shop_id' => $shop2->id]
        );

        Subscription::firstOrCreate(
            ['barber_shop_id' => $shop2->id],
            ['plan_id' => $proPlan->id, 'starts_at' => now(), 'ends_at' => now()->addYear(), 'status' => 'active', 'auto_renew' => true]
        );

        $barberUser2 = User::firstOrCreate(
            ['email' => 'staff.salon@test.com'],
            ['name' => 'Klara Stylist', 'slug' => 'klara-stylist', 'password' => Hash::make('password'), 'is_active' => true, 'barber_shop_id' => $shop2->id]
        );
        $barber2 = Barber::updateOrCreate(
            ['barber_shop_id' => $shop2->id, 'user_id' => $barberUser2->id],
            ['name' => 'Klara Stylist', 'phone' => '+355692222222', 'photo' => 'barber2.png', 'bio' => 'Specialiste për lyerje, balayage dhe trajtime keratine.', 'active' => true]
        );

        $service2_1 = Service::updateOrCreate(
            ['barber_shop_id' => $shop2->id, 'name' => 'Qethje & Modelim Femrash'],
            ['description' => 'Prerje sipas formës së fytyrës, larje dhe krehje me furçë.', 'price' => 2000.00, 'duration_minutes' => 45, 'category' => 'Flokë Femra', 'active' => true]
        );
        $service2_2 = Service::updateOrCreate(
            ['barber_shop_id' => $shop2->id, 'name' => 'Lyerje Flokësh + Breshing'],
            ['description' => 'Lyerje e plotë me bojëra profesionale pa amoniak.', 'price' => 4000.00, 'duration_minutes' => 90, 'category' => 'Koloristikë', 'active' => true]
        );

        $this->seedHoursAndEvents($shop2->id, $barber2->id, $events);


        // =========================================================================
        // BUSINESS 3: 💅 NAIL STUDIO & ESTHETICS ("Glamour Nail Studio")
        // =========================================================================
        $owner3 = User::firstOrCreate(
            ['email' => 'owner.nails@test.com'],
            [
                'name' => 'Sonia Nails',
                'slug' => 'sonia-nails',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $shop3 = BarberShop::updateOrCreate(
            ['slug' => 'glamour-nail-studio'],
            [
                'owner_id' => $owner3->id,
                'name' => 'Glamour Nail Studio',
                'app_name' => 'Glamour Nails',
                'logo' => 'placeholder.png',
                'banner' => 'placeholder.png',
                'primary_color' => '#10B981',
                'secondary_color' => '#F43F5E',
                'business_type' => 'nail_studio',
                'staff_label' => 'Teknik/e',
                'staff_label_plural' => 'Teknikët',
                'shop_label' => 'Studio Thonjsh',
                'service_label' => 'Shërbimi',
                'trial_ends_at' => now()->addDays(30),
                'expires_at' => now()->addYear(),
                'sms_enabled' => true,
                'active' => true,
                'timezone' => 'Europe/Tirane',
                'max_no_show_before_block' => 3,
            ]
        );

        $owner3->update(['barber_shop_id' => $shop3->id]);
        $owner3->barberShops()->syncWithoutDetaching([$shop3->id]);
        DB::table('model_has_roles')->updateOrInsert(
            ['role_id' => $ownerRole->id, 'model_type' => User::class, 'model_id' => $owner3->id, 'barber_shop_id' => $shop3->id],
            ['barber_shop_id' => $shop3->id]
        );

        Subscription::firstOrCreate(
            ['barber_shop_id' => $shop3->id],
            ['plan_id' => $proPlan->id, 'starts_at' => now(), 'ends_at' => now()->addYear(), 'status' => 'active', 'auto_renew' => true]
        );

        $barberUser3 = User::firstOrCreate(
            ['email' => 'staff.nails@test.com'],
            ['name' => 'Ledia NailTech', 'slug' => 'ledia-nailtech', 'password' => Hash::make('password'), 'is_active' => true, 'barber_shop_id' => $shop3->id]
        );
        $barber3 = Barber::updateOrCreate(
            ['barber_shop_id' => $shop3->id, 'user_id' => $barberUser3->id],
            ['name' => 'Ledia NailTech', 'phone' => '+355693333333', 'photo' => 'barber3.png', 'bio' => 'Speciale në artin e thonjve me xhel, akrilik dhe nail-art.', 'active' => true]
        );

        $service3_1 = Service::updateOrCreate(
            ['barber_shop_id' => $shop3->id, 'name' => 'Manikyr me Xhel & Dizajn'],
            ['description' => 'Pastrim kutikulash, trajtim me xhel dhe dizajn të personalizuar.', 'price' => 2500.00, 'duration_minutes' => 60, 'category' => 'Thonj', 'active' => true]
        );
        $service3_2 = Service::updateOrCreate(
            ['barber_shop_id' => $shop3->id, 'name' => 'Pedikyr Estetik & Spa'],
            ['description' => 'Trajtim hidratues me parafinë dhe masazh për këmbët.', 'price' => 3000.00, 'duration_minutes' => 60, 'category' => 'Pedikyr', 'active' => true]
        );

        $this->seedHoursAndEvents($shop3->id, $barber3->id, $events);

        // Demo Customers & Bookings for testing
        $cust1 = Customer::updateOrCreate(
            ['barber_shop_id' => $shop1->id, 'phone' => '+355698888881'],
            ['name' => 'Albano Klienti', 'email' => 'albano@test.com', 'total_bookings' => 1, 'no_show_count' => 0]
        );
        $cust2 = Customer::updateOrCreate(
            ['barber_shop_id' => $shop2->id, 'phone' => '+355698888882'],
            ['name' => 'Anisa Kliente', 'email' => 'anisa@test.com', 'total_bookings' => 1, 'no_show_count' => 0]
        );
        $cust3 = Customer::updateOrCreate(
            ['barber_shop_id' => $shop3->id, 'phone' => '+355698888883'],
            ['name' => 'Dorina Kliente', 'email' => 'dorina@test.com', 'total_bookings' => 1, 'no_show_count' => 0]
        );

        // Demo Booking & Payment for Shop 1
        $b1 = Booking::create([
            'barber_shop_id' => $shop1->id, 'barber_id' => $barber1->id, 'service_id' => $service1_1->id, 'customer_id' => $cust1->id,
            'appointment_at' => now()->addHours(2), 'status' => 'confirmed', 'total_price' => 1000.00, 'source' => 'online',
        ]);
        Payment::create(['barber_shop_id' => $shop1->id, 'booking_id' => $b1->id, 'amount' => 1000.00, 'method' => 'cash', 'status' => 'paid']);

        // Demo Booking & Payment for Shop 2
        $b2 = Booking::create([
            'barber_shop_id' => $shop2->id, 'barber_id' => $barber2->id, 'service_id' => $service2_1->id, 'customer_id' => $cust2->id,
            'appointment_at' => now()->addHours(3), 'status' => 'confirmed', 'total_price' => 2000.00, 'source' => 'online',
        ]);
        Payment::create(['barber_shop_id' => $shop2->id, 'booking_id' => $b2->id, 'amount' => 2000.00, 'method' => 'cash', 'status' => 'paid']);

        // Demo Booking & Payment for Shop 3
        $b3 = Booking::create([
            'barber_shop_id' => $shop3->id, 'barber_id' => $barber3->id, 'service_id' => $service3_1->id, 'customer_id' => $cust3->id,
            'appointment_at' => now()->addHours(4), 'status' => 'confirmed', 'total_price' => 2500.00, 'source' => 'online',
        ]);
        Payment::create(['barber_shop_id' => $shop3->id, 'booking_id' => $b3->id, 'amount' => 2500.00, 'method' => 'cash', 'status' => 'paid']);

        $this->command->info('✅ U krijuan 3 Biznese & 3 Owners me sukses!');
        $this->command->info('1) Barber: owner.barber@test.com / password -> Gentlemen Barber Shop');
        $this->command->info('2) Salon: owner.salon@test.com / password -> Elegance Beauty Salon');
        $this->command->info('3) Nails: owner.nails@test.com / password -> Glamour Nail Studio');
    }

    private function seedHoursAndEvents(int $shopId, int $barberId, $events): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $day) {
            WorkingHour::updateOrCreate(
                ['barber_id' => $barberId, 'day_of_week' => $day],
                ['open_time' => '09:00', 'close_time' => '19:00', 'lunch_start' => '13:00', 'lunch_end' => '14:00', 'is_closed' => false]
            );
        }

        NotificationChannel::updateOrCreate(
            ['barber_shop_id' => $shopId, 'channel' => 'sms'],
            ['enabled' => true, 'daily_limit' => 200]
        );

        foreach ($events as $evt) {
            EventSetting::updateOrCreate(
                ['barber_shop_id' => $shopId, 'realtime_event_id' => $evt->id],
                ['reverb_enabled' => true, 'firebase_enabled' => true]
            );
        }

        MessageTemplate::updateOrCreate(
            ['barber_shop_id' => $shopId, 'type' => 'confirmation'],
            ['channel' => 'sms', 'content' => 'Përshëndetje {customer_name}! Rezervimi juaj për {service_name} me {staff_name} në {shop_name} u konfirmua për orën {time}. Faleminderit!']
        );
        MessageTemplate::updateOrCreate(
            ['barber_shop_id' => $shopId, 'type' => 'reminder'],
            ['channel' => 'sms', 'content' => 'Përshëndetje {customer_name}, ju kujtojmë takimin tuaj për {service_name} në {shop_name} sot në orën {time}.']
        );
    }
}
