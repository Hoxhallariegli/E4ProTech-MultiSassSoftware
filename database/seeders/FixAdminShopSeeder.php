<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BarberShop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixAdminShopSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@e4protech.com')->first();
        $shops = BarberShop::all();

        if ($user && $shops->isNotEmpty()) {
            $firstShop = $shops->first();

            // Set active shop
            $user->update(['barber_shop_id' => $firstShop->id]);

            // Link to all shops in the pivot table so the switcher shows all of them
            $user->barberShops()->sync($shops->pluck('id')->toArray());

            // Assign admin role for each shop team
            $roleAdmin = \App\Models\Role::where('name', 'admin')->first();
            if ($roleAdmin) {
                foreach ($shops as $shop) {
                    DB::table('model_has_roles')->updateOrInsert(
                        [
                            'model_id' => $user->id,
                            'model_type' => 'App\Models\User',
                            'role_id' => $roleAdmin->id,
                            'barber_shop_id' => $shop->id
                        ],
                        ['barber_shop_id' => $shop->id]
                    );
                }
            }

            $this->command->info("SUCCESS: Admin linked to " . $shops->count() . " shops. Active ID: " . $firstShop->id);
        } else {
            $this->command->error("ERROR: User 'demo@e4protech.com' or Shops not found.");
        }
    }
}
