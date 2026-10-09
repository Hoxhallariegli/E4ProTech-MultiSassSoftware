<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModule extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'qty', 'price', 'is_active', 'due_date', 'event_at', 'user_id', 'priority', 'image', 'cover_photo'];
    protected function casts(): array { return [
            'qty' => 'decimal:2',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'due_date' => 'datetime',
            'event_at' => 'datetime',
        ]; }
    public static function rules($id = null): array { return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'qty' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'is_active' => ['boolean'],
            'due_date' => ['required', 'date'],
            'event_at' => ['required', 'date'],
            'user_id' => ['required', 'string'],
            'priority' => ['required', \Illuminate\Validation\Rule::in(['Low', 'Medium', 'High'])],
            'image' => ['nullable', 'string', 'max:255'],
            'cover_photo' => ['nullable', 'string', 'max:255'],
        ]; }
    public static function sortable(): array { return ['id', 'name', 'description', 'qty', 'price', 'is_active', 'due_date', 'event_at', 'user_id', 'priority', 'image', 'cover_photo']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\TestModuleObserver::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\User::class, 'user_id'); }

}