<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use HasUuid;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'image',
        'is_office_login_only',
        'is_active',
        'barber_shop_id',
        'email_verified_at',
        'last_logged_in_at',
        'two_fa_active',
        'two_fa_secret_key',
        'invited_by',
        'invited_at',
        'joined_at',
        'invite_token',
        'last_activity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Local Scope to filter users by the active shop members.
     * Use this manually in controllers/livewire to avoid Auth recursion.
     */
    public function scopeForActiveShop($query)
    {
        if (app()->runningInConsole() || !auth()->check()) {
            return $query;
        }

        $user = auth()->user();

        // Admin sees everyone
        $isGlobalAdmin = \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('barber_shop_id', 0)
            ->exists();

        if ($isGlobalAdmin) {
            return $query;
        }

        return $query->whereHas('barberShops', function($q) use ($user) {
            $q->where('barber_shops.id', $user->barber_shop_id);
        });
    }

    public function route(string $id): string
    {
        return route('admin.users.show', ['user' => $id]);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BarberShop::class, 'barber_shop_id');
    }

    public function barberShops(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(BarberShop::class, 'barber_shop_user');
    }

    /**
     * Determine if the user is a Global Super Admin (Team 0).
     */
    public function getIsGlobalAdminAttribute(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember('is_global_admin_' . $this->id, 3600, function() {
            return \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->where('model_id', $this->id)
                ->where('barber_shop_id', 0)
                ->exists();
        });
    }

    /**
     * Get permissions for the user, strictly scoped to the active team.
     */
    public function getPermissionsFlattened(): \Illuminate\Support\Collection
    {
        return $this->getAllPermissions()->pluck('name');
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    public function invite(): HasOne
    {
        return $this->hasOne(self::class, 'id', 'invited_by');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_logged_in_at' => 'datetime',
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
            'last_activity' => 'datetime',
            'two_fa_active' => 'boolean',
            'is_active' => 'boolean',
            'is_office_login_only' => 'boolean',
        ];
    }
}
