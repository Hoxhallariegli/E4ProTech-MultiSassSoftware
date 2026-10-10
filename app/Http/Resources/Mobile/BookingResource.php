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

        $smsMessages = \App\Models\MessageQueue::where('booking_id', $this->id)
            ->get()
            ->map(function ($queue) {
                $type = $queue->resolved_template_type;
                $typeLabel = match($type) {
                    'reminder' => 'Rikujtesë SMS',
                    'reschedule', 'update' => 'Ndryshim SMS',
                    'cancellation', 'cancelled' => 'Anulim SMS',
                    'welcome' => 'Mirëseardhje SMS',
                    default => 'Konfirmim SMS',
                };
                return [
                    'id' => $queue->id,
                    'type' => $type,
                    'type_label' => $typeLabel,
                    'message_content' => $queue->message_content,
                    'phone_number' => $queue->phone_number,
                    'status' => $queue->status,
                    'scheduled_at' => $queue->scheduled_at?->toIso8601String(),
                    'created_at' => $queue->created_at?->toIso8601String(),
                    'updated_at' => $queue->updated_at?->toIso8601String(),
                ];
            })
            ->values()
            ->toArray();

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
            'sms_messages' => $smsMessages,
            'barberShop' => $this->whenLoaded('barberShop'),
            'barber' => $this->whenLoaded('barber'),
            'service' => $this->whenLoaded('service'),
            'customer' => $this->whenLoaded('customer'),
        ];
    }
}
