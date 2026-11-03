<?php

namespace App\Domain\BulkCancellation\Actions;

use App\Models\BulkCancellation;
use App\Domain\BulkCancellation\DTOs\BulkCancellationDTO;
use App\Models\Booking;
use App\Models\MessageQueue;
use App\Models\MessageTemplate;
use App\Models\AuditTrail;
use App\Jobs\SendSmsToGatewayJob;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CreateBulkCancellationAction
{
    public function execute(BulkCancellationDTO $dto): BulkCancellation
    {
        $data = $dto->toArray();
        $item = BulkCancellation::create($data);
        AuditTrail::log($item, 'create', 'BulkCancellations');

        if ($item->barber_id && $item->start_at && $item->end_at) {
            $start = Carbon::parse($item->start_at);
            $end = Carbon::parse($item->end_at);

            $query = Booking::query()
                ->with(['customer', 'service', 'barber', 'barberShop'])
                ->where('barber_id', $item->barber_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereBetween('appointment_at', [$start, $end]);

            if ($item->barber_shop_id) {
                $query->where('barber_shop_id', $item->barber_shop_id);
            }

            $affectedBookings = $query->orderBy('appointment_at', 'asc')->get();
            $affectedCount = $affectedBookings->count();

            $cancelledBookingIds = [];

            foreach ($affectedBookings as $booking) {
                $cancelledBookingIds[] = $booking->id;

                // 1. Delete all existing pending SMS queue items (reminders, etc.) for this booking
                MessageQueue::where('booking_id', $booking->id)
                    ->where('status', 'pending')
                    ->delete();

                // 2. Mark booking as cancelled
                $booking->update([
                    'status' => 'cancelled',
                    'notes' => trim(($booking->notes ? $booking->notes . ' | ' : '') . 'Anuluar për mungesë stafi: ' . ($item->reason ?: 'Mungesë emergjente')),
                ]);

                // 3. Determine preferred channel (WhatsApp or SMS) and parse template
                $shop = $booking->barberShop;
                $barber = $booking->barber;
                $preferredChannel = ($barber?->whatsapp_enabled ?? true) || ($shop?->whatsapp_enabled ?? true) ? 'whatsapp' : 'sms';
                $parsedMessage = MessageTemplate::parseForBooking($booking, 'cancellation', null, $preferredChannel);

                // 4. Send Cancellation Notification IMMEDIATELY
                if ($booking->customer && $booking->customer->phone) {
                    $formattedPhone = $booking->customer->phone;

                    $queueData = [
                        'barber_shop_id' => $booking->barber_shop_id,
                        'booking_id' => $booking->id,
                        'channel' => $preferredChannel,
                        'phone_number' => $formattedPhone,
                        'message_content' => $parsedMessage,
                        'scheduled_at' => now(),
                        'status' => 'pending',
                        'retry_count' => 0,
                    ];
                    if (Schema::hasColumn('message_queues', 'template_type')) {
                        $queueData['template_type'] = 'cancellation';
                    }
                    $queue = MessageQueue::create($queueData);

                    // Dispatch immediate Gateway dispatch
                    try {
                        SendSmsToGatewayJob::dispatch($queue->id);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Could not dispatch SendSmsToGatewayJob for Cancellation Queue #{$queue->id}: " . $e->getMessage());
                    }
                }
            }

            $item->update([
                'affected_count' => $affectedCount,
                'booking_ids' => $cancelledBookingIds,
            ]);
        }

        return $item->fresh();
    }
}
