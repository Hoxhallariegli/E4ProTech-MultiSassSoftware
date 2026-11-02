<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $serviceName = $this->service?->name ?? 'Shërbim';
        $notes = $this->notes ?? '';
        if (str_starts_with($notes, 'Shërbimet: ')) {
            $serviceNamesString = trim(substr($notes, strlen('Shërbimet: ')));
            $names = array_map('trim', explode('+', $serviceNamesString));
            if (!empty($names)) {
                $serviceName = implode(' + ', $names);
            }
        }

        $paymentRecord = \App\Models\Payment::where('booking_id', $this->id)->latest()->first();
        $paymentStatus = $paymentRecord?->status ?? $this->payment_status ?? 'unpaid';

        $displayPrice = ($paymentRecord && (float) $paymentRecord->amount > 0)
            ? $paymentRecord->amount
            : (($this->total_price && (float) $this->total_price > 0)
                ? $this->total_price
                : ($this->service?->price ?? 0));

        // 1. Fetch Sent / Delivered Logs from MessageLog (by booking_id or customer_id fallback)
        $messageLogs = \App\Models\MessageLog::where('booking_id', $this->id)
            ->orWhere(function($q) {
                if ($this->customer_id) {
                    $q->where('customer_id', $this->customer_id)
                      ->where('created_at', '>=', $this->created_at?->subMinutes(10) ?? now()->subDays(1));
                }
            })
            ->latest()
            ->get()
            ->map(function ($log) {
                $channel = strtolower($log->channel ?? 'whatsapp');
                $channelLabel = $channel === 'whatsapp' ? 'WhatsApp' : 'SMS';
                $type = $log->template_type ?? 'confirmation';
                $typeLabel = match($type) {
                    'reminder' => "Rikujtesë {$channelLabel}",
                    'reschedule', 'update' => "Ndryshim {$channelLabel}",
                    'cancellation', 'cancelled' => "Anulim {$channelLabel}",
                    'welcome' => "Mirëseardhje {$channelLabel}",
                    default => "Konfirmim {$channelLabel}",
                };
                return [
                    'id' => $log->id,
                    'type' => $type,
                    'type_label' => $typeLabel,
                    'channel' => $channel,
                    'message_content' => $log->message_content ?? $log->message ?? '',
                    'phone_number' => $log->phone_number ?? $log->recipient ?? '',
                    'status' => 'sent',
                    'scheduled_at' => $log->created_at?->toIso8601String(),
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            });

        // 2. Fetch Pending Queue Items from MessageQueue (WhatsApp & SMS)
        $messageQueues = \App\Models\MessageQueue::where('booking_id', $this->id)
            ->get()
            ->map(function ($queue) {
                $channel = strtolower($queue->channel ?? 'whatsapp');
                $channelLabel = $channel === 'whatsapp' ? 'WhatsApp' : 'SMS';
                $type = $queue->resolved_template_type ?? $queue->template_type ?? 'reminder';
                $typeLabel = match($type) {
                    'reminder' => "Rikujtesë {$channelLabel}",
                    'reschedule', 'update' => "Ndryshim {$channelLabel}",
                    'cancellation', 'cancelled' => "Anulim {$channelLabel}",
                    'welcome' => "Mirëseardhje {$channelLabel}",
                    default => "Konfirmim {$channelLabel}",
                };
                return [
                    'id' => $queue->id,
                    'type' => $type,
                    'type_label' => $typeLabel,
                    'channel' => $channel,
                    'message_content' => $queue->message_content ?? '',
                    'phone_number' => $queue->phone_number ?? '',
                    'status' => strtolower($queue->status ?? 'pending'),
                    'scheduled_at' => $queue->scheduled_at?->toIso8601String(),
                    'created_at' => $queue->created_at?->toIso8601String(),
                ];
            });

        // Combine sent logs and pending queue into unified notification list
        $combinedMessages = $messageLogs->concat($messageQueues)->values()->toArray();

        return [
            'id' => $this->id,
            'name' => 'Takimi #' . $this->id . ' - ' . ($this->customer?->name ?? 'Klient') . ' (' . $serviceName . ' - ' . $displayPrice . ' Lekë)',
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
            'appointment_at' => $this->appointment_at,
            'status' => $this->status,
            'payment_status' => $paymentStatus,
            'total_price' => $displayPrice,
            'notes' => $this->notes,
            'source' => $this->source,
            'service_name' => $serviceName,
            'sms_messages' => $combinedMessages,
            'notification_messages' => $combinedMessages,
            'barberShop' => $this->whenLoaded('barberShop'),
            'barber' => $this->whenLoaded('barber'),
            'service' => $this->whenLoaded('service'),
            'customer' => $this->whenLoaded('customer'),
        ];
    }
}
