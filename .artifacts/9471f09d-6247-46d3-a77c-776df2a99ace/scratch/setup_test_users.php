<?php

use App\Models\User;
use App\Models\BarberShop;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// 1. Sigurohu që rolet ekzistojnë (duke përdorur modelin tënd me UUID dhe label)
$adminRole = Role::firstOrCreate(
    ['name' => 'admin'],
    ['guard_name' => 'web', 'label' => 'Administrator']
);
$ownerRole = Role::firstOrCreate(
    ['name' => 'owner'],
    ['guard_name' => 'web', 'label' => 'Pronar Dyqani']
);

// 2. Krijojmë Adminin Kryesor
$admin = User::firstOrCreate(
    ['email' => 'admin@laraauto.com'],
    [
        'name' => 'Admin Kryesor',
        'slug' => 'admin-kryesor',
        'password' => Hash::make('password'),
        'is_active' => true
    ]
);
$admin->assignRole($adminRole);

// 3. Krijojmë dy dyqane testuese
$shop1 = BarberShop::firstOrCreate(['name' => 'Berberana Tirana'], ['active' => true]);
$shop2 = BarberShop::firstOrCreate(['name' => 'Berberana Durres'], ['active' => true]);

// 4. Krijojmë një Pronar për çdo dyqan
$owner1 = User::firstOrCreate(
    ['email' => 'owner1@gmail.com'],
    [
        'name' => 'Pronari Tiranes',
        'slug' => 'pronari-tiranes',
        'password' => Hash::make('password'),
        'barber_shop_id' => $shop1->id,
        'is_active' => true
    ]
);
$owner1->assignRole($ownerRole);

$owner2 = User::firstOrCreate(
    ['email' => 'owner2@gmail.com'],
    [
        'name' => 'Pronari Durresit',
        'slug' => 'pronari-durresit',
        'password' => Hash::make('password'),
        'barber_shop_id' => $shop2->id,
        'is_active' => true
    ]
);
$owner2->assignRole($ownerRole);

echo "Test Setup Completed Successfully!\n";
echo "Admin: admin@laraauto.com / password\n";
echo "Owner 1: owner1@gmail.com / password (Shop: Tirana)\n";
echo "Owner 2: owner2@gmail.com / password (Shop: Durres)\n";
