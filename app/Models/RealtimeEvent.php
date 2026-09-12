<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RealtimeEvent extends Model
{
    protected $fillable = ['event', 'label', 'channel', 'description', 'firebase_enabled'];

    protected function casts(): array
    {
        return ['firebase_enabled' => 'boolean'];
    }

    /**
     * Fast, cached check used on every model event — no DB hit per request.
     * $event is e.g. 'job-cards.created'.
     */
    public static function firebaseEnabled(string $event): bool
    {
        return (bool) Cache::rememberForever(
            static::cacheKey($event),
            fn () => static::where('event', $event)->value('firebase_enabled') ?? false
        );
    }

    protected static function cacheKey(string $event): string
    {
        return "realtime_event:firebase:{$event}";
    }

    protected static function booted(): void
    {
        static::saved(fn (self $row) => Cache::forget(self::cacheKey($row->event)));
        static::deleted(fn (self $row) => Cache::forget(self::cacheKey($row->event)));
    }
}
