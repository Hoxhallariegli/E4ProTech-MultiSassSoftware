<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BarberShop;
use App\Models\User;
use App\Models\Barber;
use App\Models\Service;
use App\Models\WorkingHour;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CreateSalonWizard extends Component
{
    public $step = 1;

    // Step 1: Salon Info
    public $salonName = '';
    public $salonSlug = '';
    public $businessType = 'barber';

    // Step 2: Owner Info
    public $ownerName = '';
    public $ownerPhone = '';
    public $email = '';
    public $password = '';

    // Success State
    public $createdShop = null;

    public function updatedSalonName()
    {
        $this->salonSlug = Str::slug($this->salonName);
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'salonName' => 'required|string|max:100',
                'salonSlug' => 'required|string|max:100|unique:barber_shops,slug',
                'businessType' => 'required|string',
            ], [
                'salonName.required' => 'Ju lutemi vendosni emrin e sallonit tuaj.',
                'salonSlug.required' => 'Ju lutemi vendosni adresën e faqes tuaj.',
                'salonSlug.unique' => 'Kjo adresë është e zënë. Ju lutemi zgjidhni një adresë tjetër.',
            ]);

            session([
                'wizard_salon_name' => trim($this->salonName),
                'wizard_salon_slug' => Str::slug($this->salonSlug),
                'wizard_business_type' => $this->businessType,
            ]);

            $this->step = 2;
        } elseif ($this->step === 2) {
            $this->validate([
                'ownerName' => 'required|string|max:100',
                'ownerPhone' => 'required|string|max:30',
                'email' => 'required|email|max:150|unique:users,email',
                'password' => 'required|string|min:6',
            ], [
                'ownerName.required' => 'Ju lutemi vendosni Emrin dhe Mbiemrin tuaj.',
                'ownerPhone.required' => 'Ju lutemi vendosni Numrin e Telefonit.',
                'email.required' => 'Ju lutemi vendosni Adresën e E-mailit.',
                'email.unique' => 'Kjo adresë e-maili është e regjistruar tashmë.',
                'password.required' => 'Ju lutemi vendosni një fjalëkalim.',
            ]);

            $this->createSalon();
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function createSalon()
    {
        $trialEnd = now()->addDays(30);

        // Retrieve Step 1 values safely from session or properties
        $sessName = session('wizard_salon_name');
        $sessSlug = session('wizard_salon_slug');
        $sessType = session('wizard_business_type');

        $finalShopName = !empty($sessName) ? $sessName : (!empty(trim($this->salonName)) ? trim($this->salonName) : 'Sallon ' . trim($this->ownerName));
        $finalShopSlug = !empty($sessSlug) ? $sessSlug : (!empty(trim($this->salonSlug)) ? Str::slug($this->salonSlug) : Str::slug($finalShopName));
        $finalType = !empty($sessType) ? $sessType : $this->businessType;

        // 1. Create Admin/Owner User (Inactive until email verification)
        $userData = [
            'name' => trim($this->ownerName),
            'email' => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'is_active' => false, // Activated via email verification
            'email_verified_at' => null,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'slug')) {
            $userData['slug'] = Str::slug($this->ownerName) ?: Str::random(10);
        }

        $user = User::create($userData);

        // 2. Create BarberShop (Active with 30-day Free Trial)
        $shopData = [
            'owner_id' => (string) $user->id,
            'name' => $finalShopName,
            'app_name' => $finalShopName,
            'slug' => $finalShopSlug,
            'business_type' => $finalType,
            'primary_color' => '#FF9F0A',
            'secondary_color' => '#1C1C1E',
            'active' => true, // Shop is active for 30-day trial
            'sms_enabled' => true,
            'timezone' => 'Europe/Tirane',
            'trial_ends_at' => $trialEnd,
            'expires_at' => $trialEnd,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('barber_shops', 'status')) {
            $shopData['status'] = 'active';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('barber_shops', 'phone')) {
            $shopData['phone'] = trim($this->ownerPhone);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('barber_shops', 'email')) {
            $shopData['email'] = strtolower(trim($this->email));
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('barber_shops', 'reminder_hours_before')) {
            $shopData['reminder_hours_before'] = 2;
        }

        $shop = BarberShop::create($shopData);

        // Link shop_id to user
        $user->update(['barber_shop_id' => $shop->id]);

        if (method_exists($shop, 'users')) {
            try { $shop->users()->syncWithoutDetaching([$user->id]); } catch (\Throwable $e) {}
        }

        // 3. Create & Assign Spatie Roles ("pronar_i_biznesit_/_sallonit" & "admin")
        try {
            setPermissionsTeamId($shop->id);

            $ownerRole = Role::firstOrCreate([
                'name' => 'pronar_i_biznesit_/_sallonit',
                'label' => 'Pronar i Biznesit / Sallonit',
            ]);

            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'label' => 'Admin',
            ]);

            $allPermissions = Permission::all();
            if ($allPermissions->isNotEmpty()) {
                try { $ownerRole->syncPermissions($allPermissions); } catch (\Throwable $e) {}
                try { $adminRole->syncPermissions($allPermissions); } catch (\Throwable $e) {}
            }

            $user->assignRole('pronar_i_biznesit_/_sallonit');
            $user->assignRole('admin');

            // Failsafe global team
            setPermissionsTeamId(0);
            try { $user->assignRole('pronar_i_biznesit_/_sallonit'); } catch (\Throwable $e) {}
            try { $user->assignRole('admin'); } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Role assignment warning: " . $e->getMessage());
        }

        // 4. Create 30-Day Free Trial Subscription with 'Trial' Plan
        $trialPlan = Plan::where('name', 'Trial')->orWhere('price', 0)->first() ?? Plan::first();
        if ($trialPlan) {
            try {
                Subscription::create([
                    'barber_shop_id' => $shop->id,
                    'plan_id' => $trialPlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => $trialEnd,
                ]);
            } catch (\Throwable $e) {}
        }

        // 5. Create Default Staff (Owner)
        $barber = Barber::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $user->id,
            'name' => trim($this->ownerName),
            'phone' => trim($this->ownerPhone),
            'active' => true,
        ]);

        // 6. Create Default Working Hours for Owner
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $day) {
            WorkingHour::create([
                'barber_id' => $barber->id,
                'day_of_week' => $day,
                'open_time' => '09:00:00',
                'close_time' => '19:00:00',
                'lunch_start' => '13:00:00',
                'lunch_end' => '14:00:00',
                'is_closed' => false,
            ]);
        }

        // 7. Create Default Sample Service
        Service::create([
            'barber_shop_id' => $shop->id,
            'name' => 'Shërbim Kryesor',
            'description' => 'Prerje dhe stilim profesional.',
            'price' => 1500,
            'duration_minutes' => 30,
            'active' => true,
        ]);

        // Send Custom E4ProTech Branded Registration Email Notification
        try {
            $user->notify(new \App\Notifications\SalonRegisteredNotification($shop));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Email notification sending warning: " . $e->getMessage());
        }

        // Clean session
        session()->forget(['wizard_salon_name', 'wizard_salon_slug', 'wizard_business_type']);

        $this->createdShop = $shop;
        $this->step = 3;
    }

    public function render()
    {
        return view('livewire.create-salon-wizard')->layout('components.layouts.blank');
    }
}
