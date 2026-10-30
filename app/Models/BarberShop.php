<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'app_name',
        'slug',
        'logo',
        'banner',
        'phone',
        'email',
        'status',
        'reminder_hours_before',
        'primary_color',
        'secondary_color',
        'trial_ends_at',
        'expires_at',
        'active',
        'sms_enabled',
        'whatsapp_enabled',
        'timezone',
        'max_no_show_before_block',
        'min_service_time',
        'business_type',
        'staff_label',
        'staff_label_plural',
        'shop_label',
        'service_label',
        'currency',
    ];

    protected function casts(): array {
        return [
            'trial_ends_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
            'sms_enabled' => 'boolean',
            'reminder_hours_before' => 'float',
            'max_no_show_before_block' => 'integer',
            'min_service_time' => 'integer',
        ];
    }

    public static function rules($id = null): array {
        return [
            'owner_id' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'app_name' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'banner' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'max:255'],
            'secondary_color' => ['required', 'string', 'max:255'],
            'trial_ends_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'active' => ['boolean'],
            'sms_enabled' => ['boolean'],
            'reminder_hours_before' => ['nullable', 'numeric', 'min:0.1', 'max:168'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'max_no_show_before_block' => ['nullable', 'integer'],
            'min_service_time' => ['nullable', 'integer', 'min:1', 'max:480'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'staff_label' => ['nullable', 'string', 'max:255'],
            'staff_label_plural' => ['nullable', 'string', 'max:255'],
            'shop_label' => ['nullable', 'string', 'max:255'],
            'service_label' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:50'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'owner_id', 'name', 'app_name', 'business_type', 'active', 'timezone', 'reminder_hours_before'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\BarberShopObserver::class);
    }

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(User::class, 'barber_shop_user');
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(\App\Models\Subscription::class, 'barber_shop_id');
    }

    public function frontPageSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\ShopFrontPageSetting::class, 'barber_shop_id');
    }

    public function getActivePlanNameAttribute(): string
    {
        $sub = $this->subscriptions()->with('plan')->latest('ends_at')->first();
        if ($sub && $sub->ends_at) {
            if ($sub->ends_at->isPast()) {
                return __('admin.Subscription Expired');
            }
            return $sub->plan?->name ?? __('admin.Active Plan');
        }

        if ($this->expires_at) {
            if ($this->expires_at->isPast()) return __('admin.Subscription Expired');
            return __('admin.Active Plan');
        }

        if ($this->trial_ends_at) {
            if ($this->trial_ends_at->isPast()) return __('admin.Subscription Expired');
            return __('admin.Free Trial (30 Days)');
        }

        return __('admin.Subscription Expired');
    }

    public function getDaysLeftAttribute(): int
    {
        // 1. Check latest subscription from subscriptions table
        $sub = $this->subscriptions()->latest('ends_at')->first();
        if ($sub && $sub->ends_at) {
            if ($sub->ends_at->isPast()) {
                return 0; // Expired!
            }
            return (int) max(0, (int) ceil(now()->diffInDays($sub->ends_at, false)));
        }

        // 2. Fallback to direct shop attributes if no subscription exists
        if ($this->expires_at) {
            if ($this->expires_at->isPast()) return 0;
            return (int) max(0, (int) ceil(now()->diffInDays($this->expires_at, false)));
        }

        if ($this->trial_ends_at) {
            if ($this->trial_ends_at->isPast()) return 0;
            return (int) max(0, (int) ceil(now()->diffInDays($this->trial_ends_at, false)));
        }

        return 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->getDaysLeftAttribute() <= 0;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    public function getLogoUrlAttribute() {
        if (empty($this->logo)) return asset('placeholder.png');
        if (str_starts_with($this->logo, 'http')) return $this->logo;
        $file = ltrim(str_replace(['\\', 'storage/'], ['/', ''], $this->logo), '/');
        return asset($file);
    }

    public function getResolvedStaffLabelAttribute(): string
    {
        if (!empty($this->staff_label)) return $this->staff_label;
        $isEn = app()->getLocale() === 'en';
        return match($this->business_type ?? 'general') {
            'beauty_salon' => $isEn ? 'Hair Stylist' : 'Parukier/e',
            'nail_studio' => $isEn ? 'Nail Technician' : 'Teknik/e',
            'spa', 'aesthetic' => $isEn ? 'Specialist' : 'Specialist/e',
            'barbershop', 'barber' => $isEn ? 'Barber' : 'Berber',
            default => $isEn ? 'Staff Member' : 'Punonjësi',
        };
    }

    public function getResolvedStaffLabelPluralAttribute(): string
    {
        if (!empty($this->staff_label_plural)) return $this->staff_label_plural;
        $isEn = app()->getLocale() === 'en';
        return match($this->business_type ?? 'general') {
            'beauty_salon' => $isEn ? 'Hair Stylists' : 'Parukierët',
            'nail_studio' => $isEn ? 'Technicians' : 'Teknikët',
            'spa', 'aesthetic' => $isEn ? 'Specialists' : 'Specialistët',
            'barbershop', 'barber' => $isEn ? 'Barbers' : 'Berberët',
            default => $isEn ? 'Staff Members' : 'Punonjësit',
        };
    }

    public function getResolvedShopLabelAttribute(): string
    {
        if (!empty($this->shop_label)) return $this->shop_label;
        $isEn = app()->getLocale() === 'en';
        return match($this->business_type ?? 'general') {
            'beauty_salon' => $isEn ? 'Beauty Salon' : 'Sallon Bukurie',
            'nail_studio' => $isEn ? 'Nail Studio' : 'Studio Thonjsh',
            'spa', 'aesthetic' => $isEn ? 'Spa Center' : 'Qendër Estetike',
            'barbershop', 'barber' => $isEn ? 'Barber Shop' : 'Berberanë',
            default => $isEn ? 'Official Shop' : 'Dyqani',
        };
    }

    public function getResolvedShopLabelPluralAttribute(): string
    {
        $isEn = app()->getLocale() === 'en';
        return match($this->business_type ?? 'general') {
            'beauty_salon' => $isEn ? 'Beauty Salons' : 'Sallonet e Bukurisë',
            'nail_studio' => $isEn ? 'Nail Studios' : 'Studiot e Thonjve',
            'spa', 'aesthetic' => $isEn ? 'Spa Centers' : 'Qendrat Estetike',
            'barbershop', 'barber' => $isEn ? 'Barber Shops' : 'Berberanat',
            default => $isEn ? 'Official Shops' : 'Dyqanet',
        };
    }

    public function getResolvedServiceLabelAttribute(): string
    {
        if (!empty($this->service_label)) return $this->service_label;
        $isEn = app()->getLocale() === 'en';
        return match($this->business_type ?? 'general') {
            'spa', 'aesthetic' => $isEn ? 'Treatment' : 'Trajtimi',
            default => $isEn ? 'Service' : 'Shërbimi',
        };
    }

    public function getResolvedCurrencyAttribute(): string
    {
        return !empty($this->currency) ? $this->currency : 'Lekë';
    }

    public function getResolvedMinServiceTimeAttribute(): int
    {
        if ($this->min_service_time && (int) $this->min_service_time > 0) {
            return (int) $this->min_service_time;
        }

        $min = \App\Models\Service::withoutGlobalScope('barber_shop_access')
            ->where('barber_shop_id', $this->id)
            ->where('active', true)
            ->where('duration_minutes', '>', 0)
            ->min('duration_minutes');

        return $min && (int) $min > 0 ? (int) $min : 15;
    }
}
