<?php

use App\Models\User;
use App\Models\BarberShop;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// 1. Roles
$ownerRole = Role::firstOrCreate(['name' => 'owner'], ['guard_name' => 'web', 'label' => 'Pronar Dyqani']);

// 2. Demo User 1 (2 Dyqane)
$user1 = User::firstOrCreate(
    ['email' => 'user1@demo.com'],
    [
        'name' => 'User One (2 Shops)',
        'slug' => 'user-one',
        'password' => Hash::make('password'),
        'is_active' => true
    ]
);
$user1->assignRole($ownerRole);

$shop1 = BarberShop::firstOrCreate(['name' => 'Elite Cut Tirana'], ['active' => true, 'address' => 'Tirana 1', 'expires_at' => Carbon::now()->addMonths(6)]);
$shop2 = BarberShop::firstOrCreate(['name' => 'Classic Style Durres'], ['active' => true, 'address' => 'Durres 1', 'expires_at' => Carbon::now()->addMonths(6)]);

// Lidhim User 1 me dy dyqanet
$user1->barberShops()->syncWithoutDetaching([$shop1->id, $shop2->id]);

// Caktojmë Shop 1 si aktiv
$user1->update(['barber_shop_id' => $shop1->id]);

// 3. Demo User 2 (1 Dyqan, Abonim Aktiv)
$user2 = User::firstOrCreate(
    ['email' => 'user2@demo.com'],
    [
        'name' => 'User Two (1 Shop)',
        'slug' => 'user-two',
        'password' => Hash::make('password'),
        'is_active' => true
    ]
);
$user2->assignRole($ownerRole);

$shop3 = BarberShop::firstOrCreate(['name' => 'Vip Barber Vlore'], ['active' => true, 'address' => 'Vlore 1', 'expires_at' => Carbon::now()->addMonth()]);
$user2->barberShops()->syncWithoutDetaching([$shop3->id]);
$user2->update(['barber_shop_id' => $shop3->id]);

// 4. Demo User 3 (Abonim i Skaduar për test)
$user3 = User::firstOrCreate(
    ['email' => 'user3@expired.com'],
    [
        'name' => 'User Expired',
        'slug' => 'user-expired',
        'password' => Hash::make('password'),
        'is_active' => true
    ]
);
$user3->assignRole($ownerRole);
$shopExpired = BarberShop::firstOrCreate(['name' => 'Old Cut Shop'], ['active' => true, 'address' => 'Prishtine', 'expires_at' => Carbon::now()->subDays(5)]);
$user3->barberShops()->syncWithoutDetaching([$shopExpired->id]);
$user3->update(['barber_shop_id' => $shopExpired->id]);

echo "Demo Data Created Successfully!\n";
echo "-------------------------------\n";
echo "User 1 (2 Shops): user1@demo.com / password\n";
echo "User 2 (1 Shop, Active Sub): user2@demo.com / password\n";
echo "User 3 (Expired Sub): user3@expired.com / password\n";
