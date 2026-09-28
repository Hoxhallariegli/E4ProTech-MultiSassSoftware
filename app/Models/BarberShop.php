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
        'primary_color',
        'secondary_color',
        'trial_ends_at',
        'expires_at',
        'active',
        'sms_enabled',
        'timezone',
        'max_no_show_before_block',
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
        ];
    }

    public static function rules($id = null): array {
        return [
            'owner_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'app_name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'banner' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'max:255'],
            'secondary_color' => ['required', 'string', 'max:255'],
            'trial_ends_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'active' => ['boolean'],
            'sms_enabled' => ['boolean'],
            'timezone' => ['required', 'string', 'max:255'],
            'max_no_show_before_block' => ['nullable', 'integer'],
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
        $sub = $this->subscriptions()->with('plan')->where('status', 'active')->where('ends_at', '>=', now())->latest()->first();
        if ($sub && $sub->plan) {
            return $sub->plan->name;
        }
        return 'Plani Aktiv';
    }

    public function getDaysLeftAttribute(): int
    {
        $sub = $this->subscriptions()->where('status', 'active')->where('ends_at', '>=', now())->latest()->first();
        if ($sub && $sub->ends_at) {
            return (int) max(0, now()->diffInDays($sub->ends_at, false));
        }
        if ($this->expires_at) {
            return (int) max(0, now()->diffInDays($this->expires_at, false));
        }
        return 0;
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
}
