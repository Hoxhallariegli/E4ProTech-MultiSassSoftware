<?php

namespace App\Models;

use Illuminate\Database\Factories\HasFactory;
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
     * Parses the message template for a booking based on the customer's preferred language.
     * Falls back to default env language (sq/en) if not specified.
     */
    public static function parseForBooking(Booking $booking, string $type = 'confirmation', ?string $locale = null): string
    {
        $shopId = $booking->barber_shop_id;

        if (!$locale) {
            $customerLang = strtolower((string) (
                $booking->customer?->language
                ?? $booking->customer?->locale
                ?? $booking->source_locale
                ?? request()->header('Accept-Language')
                ?? request()->input('locale')
                ?? request()->input('lang')
                ?? session('locale')
                ?? 'sq'
            ));

            if (str_contains($customerLang, 'en') && !str_contains($customerLang, 'sq')) {
                $locale = 'en';
            } else {
                $locale = 'sq';
            }
        }
        $locale = in_array(strtolower($locale), ['en', 'sq'], true) ? strtolower($locale) : 'sq';

        // 1. Try shop-specific template for this exact type
        $templateRecord = static::where('barber_shop_id', $shopId)
            ->where('channel', 'sms')
            ->where('type', $type)
            ->first();

        // 2. Fallback to global template (where barber_shop_id is NULL or 0) or any template for this type
        if (!$templateRecord) {
            $templateRecord = static::where(function($q) {
                    $q->whereNull('barber_shop_id')->orWhere('barber_shop_id', 0);
                })
                ->where('channel', 'sms')
                ->where('type', $type)
                ->first()
                ?? static::where('channel', 'sms')->where('type', $type)->first();
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
                    $templateText = "Reminder: Hello {customer_name}! Your appointment is today at {time}. Thank you!";
                } elseif ($type === 'reschedule' || $type === 'update') {
                    $templateText = "Hello {customer_name}! Your appointment at {shop_name} with {staff_name} is updated to {time} {date}. Thank you!";
                } elseif ($type === 'cancellation' || $type === 'cancelled') {
                    $templateText = "Hello {customer_name}! Your appointment at {shop_name} for {time} {date} was cancelled. Thank you!";
                } else {
                    $templateText = "Hello {customer_name}! Your booking for {service_name} at {shop_name} is confirmed for {time} {date}. Thank you!";
                }
            } else {
                if ($type === 'reminder') {
                    $templateText = "Rikujtese: Pershendetje {customer_name}! Takimi juaj sot ne oren {time}. Faleminderit!";
                } elseif ($type === 'reschedule' || $type === 'update') {
                    $templateText = "Pershendetje {customer_name}! Takimi juaj ne {shop_name} me {staff_name} u ndryshua per ne oren {time} {date}. Faleminderit!";
                } elseif ($type === 'cancellation' || $type === 'cancelled') {
                    $templateText = "Pershendetje {customer_name}! Takimi juaj ne {shop_name} per ne oren {time} {date} u anulua. Faleminderit!";
                } else {
                    $templateText = "Pershendetje {customer_name}! Rezervimi {service_name} ne {shop_name} u konfirmua {time} {date}. Faleminderit!";
                }
            }
        }

        $replacements = [
            '{customer_name}' => $booking->customer?->name ?? 'Klient',
            '{service_name}' => $booking->service?->name ?? 'Sherbim',
            '{staff_name}' => $booking->barber?->name ?? 'Staf',
            '{shop_name}' => $booking->barberShop?->name ?? 'Sallon',
            '{time}' => $booking->appointment_at ? $booking->appointment_at->format('H:i') : '10:00',
            '{date}' => $booking->appointment_at ? $booking->appointment_at->format('d/m/Y') : date('d/m/Y'),
        ];

        $parsed = strtr($templateText, $replacements);

        // Convert special Albanian characters (ë -> e, ç -> c) for 1 GSM SMS segment
        $parsed = strtr($parsed, [
            'ë' => 'e', 'Ë' => 'E',
            'ç' => 'c', 'Ç' => 'C',
        ]);

        return mb_substr($parsed, 0, 160);
    }
}
