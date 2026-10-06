<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageQueue extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = ['barber_shop_id', 'booking_id', 'channel', 'template_type', 'phone_number', 'message_content', 'scheduled_at', 'status', 'retry_count'];

    protected function casts(): array {
        return [
            'scheduled_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'booking_id' => ['required', 'integer'],
            'channel' => ['required', \Illuminate\Validation\Rule::in(['sms', 'whatsapp'])],
            'template_type' => ['nullable', 'string'],
            'phone_number' => ['required', 'string', 'max:255'],
            'message_content' => ['required', 'string'],
            'scheduled_at' => ['required', 'date'],
            'status' => ['required', \Illuminate\Validation\Rule::in(['pending', 'processing', 'sent', 'failed', 'skipped_limit'])],
            'retry_count' => ['required', 'integer'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'booking_id', 'channel', 'template_type', 'phone_number', 'message_content', 'scheduled_at', 'status', 'retry_count'];
    }

    public function getResolvedTemplateTypeAttribute(): string
    {
        if ($this->template_type) {
            return $this->template_type;
        }
        $content = $this->message_content ?? '';
        if (str_contains($content, 'Rikujtes') || str_contains($content, 'Reminder')) {
            return 'reminder';
        }
        if (str_contains($content, 'Mirë se erdhe') || str_contains($content, 'Welcome')) {
            return 'welcome';
        }
        return 'confirmation';
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\MessageQueueObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\Booking::class, 'booking_id');
    }
}
