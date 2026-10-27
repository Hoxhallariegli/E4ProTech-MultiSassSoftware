<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = ['barber_shop_id', 'channel', 'type', 'content'];

    protected function casts(): array {
        return [
            'content' => 'array',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'channel' => ['required', \Illuminate\Validation\Rule::in(['sms', 'whatsapp'])],
            'type' => ['required', \Illuminate\Validation\Rule::in(['reminder', 'confirmation', 'welcome', 'reschedule', 'cancellation'])],
            'content' => ['required'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'channel', 'type'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\MessageTemplateObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    /**
     * Extracts the specific locale content ('sq' or 'en') from a string that contains SQ: / EN: blocks.
     */
    public static function extractLocaleFromText(string $rawText, string $locale = 'sq'): string
    {
        $rawText = trim($rawText);

        if (preg_match('/(?:SQ|AL|SHQIP)\s*:\s*(.*?)(?=(?:EN|ENGLISH)\s*:|$)/is', $rawText) ||
            preg_match('/(?:EN|ENGLISH)\s*:\s*(.*?)(?=(?:SQ|AL|SHQIP)\s*:|$)/is', $rawText)) {

            $sqText = '';
            $enText = '';

            if (preg_match('/(?:SQ|AL|SHQIP)\s*:\s*(.*?)(?=(?:EN|ENGLISH)\s*:|$)/is', $rawText, $m)) {
                $sqText = trim($m[1]);
            }
            if (preg_match('/(?:EN|ENGLISH)\s*:\s*(.*?)(?=(?:SQ|AL|SHQIP)\s*:|$)/is', $rawText, $m)) {
                $enText = trim($m[1]);
            }

            if ($locale === 'en') {
                return !empty($enText) ? $enText : (!empty($sqText) ? $sqText : $rawText);
            } else {
                return !empty($sqText) ? $sqText : (!empty($enText) ? $enText : $rawText);
            }
        }

        return $rawText;
    }

    /**
     * Parses the message template for a booking based on the customer's preferred language & channel.
     * Supports rich WhatsApp templates with {booking_link}!
     */
    public static function parseForBooking(Booking $booking, string $type = 'confirmation', ?string $locale = null, string $channel = 'sms'): string
    {
        $shopId = $booking->barber_shop_id;

        if (!$locale) {
            $lang = data_get($booking->customer, 'language') ?? data_get($booking->customer, 'locale');
            if (!$lang) {
                $lang = session('locale')
                    ?? request()->input('locale')
                    ?? request()->input('lang')
                    ?? request()->header('Accept-Language')
                    ?? app()->getLocale();
            }

            $langStr = strtolower((string) $lang);
            if (preg_match('/^(en|english)/i', $langStr)) {
                $locale = 'en';
            } elseif (preg_match('/^(sq|al|albanian|shqip)/i', $langStr)) {
                $locale = 'sq';
            } elseif (str_contains($langStr, 'en') && !str_contains($langStr, 'sq')) {
                $locale = 'en';
            } else {
                $locale = 'sq';
            }
        }
        $locale = in_array(strtolower((string)$locale), ['en', 'sq'], true) ? strtolower((string)$locale) : 'sq';

        // 1. Try shop-specific template for requested channel ('whatsapp' or 'sms') and type
        $templateRecord = static::where('barber_shop_id', $shopId)
            ->where('channel', $channel)
            ->where('type', $type)
            ->first();

        // Fallback to channel = 'sms' if 'whatsapp' template is not explicitly created
        if (!$templateRecord && $channel === 'whatsapp') {
            $templateRecord = static::where('barber_shop_id', $shopId)
                ->where('channel', 'sms')
                ->where('type', $type)
                ->first();
        }

        // 2. Fallback to global template
        if (!$templateRecord) {
            $templateRecord = static::where(function($q) {
                    $q->whereNull('barber_shop_id')->orWhere('barber_shop_id', 0);
                })
                ->where('type', $type)
                ->first();
        }

        $contentRaw = $templateRecord?->content;
        $templateText = null;

        if ($contentRaw) {
            if (is_array($contentRaw)) {
                $templateText = $contentRaw[$locale] ?? $contentRaw['sq'] ?? $contentRaw['en'] ?? null;
            } elseif (is_string($contentRaw)) {
                $decoded = json_decode($contentRaw, true);
                if (is_array($decoded)) {
                    $templateText = $decoded[$locale] ?? $decoded['sq'] ?? $decoded['en'] ?? null;
                } else {
                    $templateText = static::extractLocaleFromText($contentRaw, $locale);
                }
            }
        }

        if (!$templateText) {
            if ($locale === 'en') {
                if ($type === 'reminder') {
                    $templateText = "Reminder: Hello {customer_name}! Your appointment at {shop_name} is today at {time}. Book or manage: {booking_link}";
                } elseif ($type === 'reschedule' || $type === 'update') {
                    $templateText = "Hello {customer_name}! Your appointment at {shop_name} with {staff_name} is updated to {time} {date}. Book or manage: {booking_link}";
                } elseif ($type === 'cancellation' || $type === 'cancelled') {
                    $templateText = "Hello {customer_name}! Your appointment at {shop_name} for {time} {date} was cancelled. Book again: {booking_link}";
                } else {
                    $templateText = "Hello {customer_name}! Your booking for {service_name} at {shop_name} is confirmed for {time} {date}. Book or manage: {booking_link}";
                }
            } else {
                if ($type === 'reminder') {
                    $templateText = "Rikujtese: Pershendetje {customer_name}! Takimi juaj te {shop_name} eshte sot ne oren {time}. Rezervo ose menaxho: {booking_link}";
                } elseif ($type === 'reschedule' || $type === 'update') {
                    $templateText = "Pershendetje {customer_name}! Takimi juaj ne {shop_name} me {staff_name} u ndryshua per ne oren {time} {date}. Rezervo ose menaxho: {booking_link}";
                } elseif ($type === 'cancellation' || $type === 'cancelled') {
                    $templateText = "Pershendetje {customer_name}! Takimi juaj ne {shop_name} per ne oren {time} {date} u anulua. Rezervo perseri: {booking_link}";
                } else {
                    $templateText = "Pershendetje {customer_name}! Rezervimi {service_name} ne {shop_name} u konfirmua per oren {time} me date {date}. Rezervo ose menaxho: {booking_link}";
                }
            }
        }

        $shopSlug = $booking->barberShop?->slug ?? 'salon';
        $bookingLink = url("/b/{$shopSlug}");

        $replacements = [
            '{customer_name}' => $booking->customer?->name ?? 'Klient',
            '{service_name}' => $booking->service?->name ?? 'Sherbim',
            '{staff_name}' => $booking->barber?->name ?? 'Staf',
            '{shop_name}' => $booking->barberShop?->name ?? 'Sallon',
            '{time}' => $booking->appointment_at ? $booking->appointment_at->format('H:i') : '10:00',
            '{date}' => $booking->appointment_at ? $booking->appointment_at->format('d/m/Y') : date('d/m/Y'),
            '{booking_link}' => $bookingLink,
            '{shop_url}' => $bookingLink,
            '{link}' => $bookingLink,
        ];

        $parsed = strtr($templateText, $replacements);

        if ($channel === 'sms') {
            $parsed = strtr($parsed, [
                'ë' => 'e', 'Ë' => 'E',
                'ç' => 'c', 'Ç' => 'C',
            ]);
            return mb_substr($parsed, 0, 160);
        }

        // WhatsApp supports rich emojis, special characters, links & full length!
        return $parsed;
    }
}
