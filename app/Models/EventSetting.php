<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'realtime_event_id', 'reverb_enabled', 'firebase_enabled'];
    protected function casts(): array { return [
            'reverb_enabled' => 'boolean',
            'firebase_enabled' => 'boolean',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'realtime_event_id' => ['required', 'integer'],
            'reverb_enabled' => ['boolean'],
            'firebase_enabled' => ['boolean'],
        ]; }
    public static function sortable(): array { return ['id', 'realtime_event_id', 'reverb_enabled', 'firebase_enabled']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\EventSettingObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public function realtimeEvent(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\RealtimeEvent::class, 'realtime_event_id'); }

}