<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price', 'duration_months', 'max_barbers', 'max_services', 'max_shops', 'active'];
    protected function casts(): array { return [
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'max_barbers' => 'integer',
            'max_services' => 'integer',
            'max_shops' => 'integer',
            'active' => 'boolean',
        ]; }
    public static function rules($id = null): array { return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'duration_months' => ['required', 'integer'],
            'max_barbers' => ['required', 'integer'],
            'max_services' => ['required', 'integer'],
            'max_shops' => ['required', 'integer'],
            'active' => ['boolean'],
        ]; }
    public static function sortable(): array { return ['id', 'name', 'price', 'duration_months', 'max_barbers', 'max_services', 'max_shops', 'active']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\PlanObserver::class);
    }

}