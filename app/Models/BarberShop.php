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
        'timezone',
        'max_no_show_before_block',
        'min_service_time',
        'business_type',
        'staff_label',
        'staff_label_plural',
        'shop_label',
        'service_label',
    ];

    protected function casts(): array {
        return [
            'trial_ends_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
            'sms_enabled' => 'boolean',
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
            'timezone' => ['nullable', 'string', 'max:255'],
            'max_no_show_before_block' => ['nullable', 'integer'],
            'min_service_time' => ['nullable', 'integer', 'min:1', 'max:480'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'staff_label' => ['nullable', 'string', 'max:255'],
            'staff_label_plural' => ['nullable', 'string', 'max:255'],
            'shop_label' => ['nullable', 'string', 'max:255'],
            'service_label' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'owner_id', 'name', 'app_name', 'business_type', 'active', 'timezone'];
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

    public function getActivePlanNameAttribute(): string
    {
        $sub = $this->subscriptions()->with('plan')->latest('ends_at')->first();
        if ($sub) {
            if ($sub->ends_at < now()) {
                return 'Abonimi Ka Skaduar ⚠️';
            }
            return $sub->plan?->name ?? 'Plani Aktiv';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Abonimi Ka Skaduar ⚠️';
        }
        if ($this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return 'Abonimi Ka Skaduar ⚠️';
        }

        return 'Trial (30 Ditë Falas)';
    }

    public function getDaysLeftAttribute(): int
    {
        $sub = $this->subscriptions()->latest('ends_at')->first();
        if ($sub) {
            if ($sub->ends_at < now()) {
                return 0; // Expired!
            }
            return (int) max(0, (int) ceil(now()->diffInDays($sub->ends_at, false)));
        }

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

    public function getLogoUrlAttribute() {
        if (empty($this->logo)) return asset('placeholder.png');
        if (str_starts_with($this->logo, 'http')) return $this->logo;
        $file = ltrim(str_replace(['\\', 'storage/'], ['/', ''], $this->logo), '/');
        return asset($file);
    }

    public function getResolvedStaffLabelAttribute(): string
    {
        if (!empty($this->staff_label)) return $this->staff_label;
        return match($this->business_type ?? 'general') {
            'beauty_salon' => 'Parukier/e',
            'nail_studio' => 'Teknik/e',
            'spa', 'aesthetic' => 'Specialist/e',
            'barbershop' => 'Berber',
            default => 'Punonjësi',
        };
    }

    public function getResolvedStaffLabelPluralAttribute(): string
    {
        if (!empty($this->staff_label_plural)) return $this->staff_label_plural;
        return match($this->business_type ?? 'general') {
            'beauty_salon' => 'Parukierët',
            'nail_studio' => 'Teknikët',
            'spa', 'aesthetic' => 'Specialistët',
            'barbershop' => 'Berberët',
            default => 'Punonjësit',
        };
    }

    public function getResolvedShopLabelAttribute(): string
    {
        if (!empty($this->shop_label)) return $this->shop_label;
        return match($this->business_type ?? 'general') {
            'beauty_salon' => 'Sallon Bukurie',
            'nail_studio' => 'Studio Thonjsh',
            'spa', 'aesthetic' => 'Qendër Estetike',
            'barbershop' => 'Berberanë',
            default => 'Dyqani',
        };
    }

    public function getResolvedServiceLabelAttribute(): string
    {
        if (!empty($this->service_label)) return $this->service_label;
        return match($this->business_type ?? 'general') {
            'spa', 'aesthetic' => 'Trajtimi',
            default => 'Shërbimi',
        };
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
