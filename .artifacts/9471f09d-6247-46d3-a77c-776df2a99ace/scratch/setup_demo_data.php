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

// ... pas krijimit të dyqaneve
$shop1 = BarberShop::firstOrCreate(['name' => 'Elite Cut Tirana'], ['active' => true, 'address' => 'Tirana 1', 'expires_at' => Carbon::now()->addMonths(6)]);
\App\Models\Subscription::updateOrCreate(
    ['barber_shop_id' => $shop1->id],
    ['plan_name' => 'Premium', 'expires_at' => $shop1->expires_at, 'status' => 'active']
);

$shopExpired = BarberShop::firstOrCreate(['name' => 'Old Cut Shop'], ['active' => true, 'address' => 'Prishtine', 'expires_at' => Carbon::now()->subDays(5)]);
\App\Models\Subscription::updateOrCreate(
    ['barber_shop_id' => $shopExpired->id],
    ['plan_name' => 'Basic', 'expires_at' => $shopExpired->expires_at, 'status' => 'expired']
);


echo "Demo Data Created Successfully!\n";
echo "-------------------------------\n";
echo "User 1 (2 Shops): user1@demo.com / password\n";
echo "User 2 (1 Shop, Active Sub): user2@demo.com / password\n";
echo "User 3 (Expired Sub): user3@expired.com / password\n";
