<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'customer_id', 'channel', 'message', 'status', 'sent_at'];
    protected function casts(): array { return [
            'sent_at' => 'datetime',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'channel' => ['required', \Illuminate\Validation\Rule::in(['sms', 'whatsapp'])],
            'message' => ['required', 'string'],
            'status' => ['required', \Illuminate\Validation\Rule::in(['sent', 'failed'])],
            'sent_at' => ['nullable', 'date'],
        ]; }
    public static function sortable(): array { return ['id', 'customer_id', 'channel', 'message', 'status', 'sent_at']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\MessageLogObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Customer::class, 'customer_id'); }

}