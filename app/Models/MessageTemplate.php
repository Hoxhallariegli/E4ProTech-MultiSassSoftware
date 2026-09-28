<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'channel', 'type', 'content'];
    protected function casts(): array { return [
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'channel' => ['required', \Illuminate\Validation\Rule::in(['sms', 'whatsapp'])],
            'type' => ['required', \Illuminate\Validation\Rule::in(['reminder', 'confirmation', 'welcome'])],
            'content' => ['required', 'string'],
        ]; }
    public static function sortable(): array { return ['id', 'channel', 'type', 'content']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\MessageTemplateObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

}