<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = ['barber_shop_id', 'channel', 'type', 'content'];

    protected function casts(): array {
        return [];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'channel' => ['required', \Illuminate\Validation\Rule::in(['sms', 'whatsapp'])],
            'type' => ['required', \Illuminate\Validation\Rule::in(['reminder', 'confirmation', 'welcome'])],
            'content' => ['required', 'string'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'channel', 'type', 'content'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\MessageTemplateObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public static function parseForBooking(Booking $booking, string $type = 'confirmation'): string
    {
        $shopId = $booking->barber_shop_id;
        $template = static::where('barber_shop_id', $shopId)
            ->where('channel', 'sms')
            ->where('type', $type)
            ->value('content');

        if (!$template) {
            $template = "Përshëndetje {customer_name}! Rezervimi juaj për {service_name} me {staff_name} në {shop_name} u konfirmua për orën {time} me datë {date}. Faleminderit!";
        }

        $replacements = [
            '{customer_name}' => $booking->customer?->name ?? 'Klient',
            '{service_name}' => $booking->service?->name ?? 'Shërbim',
            '{staff_name}' => $booking->barber?->name ?? 'Staf',
            '{shop_name}' => $booking->barberShop?->name ?? 'Sallon',
            '{time}' => $booking->appointment_at ? $booking->appointment_at->format('H:i') : '10:00',
            '{date}' => $booking->appointment_at ? $booking->appointment_at->format('d/m/Y') : date('d/m/Y'),
        ];

        return strtr($template, $replacements);
    }
}
