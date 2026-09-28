<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SimpleTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Simple Test Seeder Running...');
        $count = User::count();
        $this->command->info("User count: $count");
    }
}
