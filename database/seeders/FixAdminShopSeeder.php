<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BarberShop;
use Illuminate\Database\Seeder;

class FixAdminShopSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@e4protech.com')->first();
        $shop = BarberShop::first();

        if ($user && $shop) {
            $user->update(['barber_shop_id' => $shop->id]);
            $user->barberShops()->sync([$shop->id]);
            $this->command->info("SUCCESS: Admin linked to Shop ID: " . $shop->id);
        } else {
            $this->command->error("ERROR: User or Shop not found.");
        }
    }
}
