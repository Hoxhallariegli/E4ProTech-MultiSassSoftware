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

            $minuteOffset = 0;
            $now = now();
            $cancelledBookingIds = [];
            $firstQueueId = null;

            foreach ($affectedBookings as $booking) {
                $cancelledBookingIds[] = $booking->id;

                // 1. Mark booking as cancelled
                $booking->update([
                    'status' => 'cancelled',
                    'notes' => trim(($booking->notes ? $booking->notes . ' | ' : '') . 'Anuluar për mungesë stafi: ' . ($item->reason ?: 'Mungesë emergjente')),
                ]);

                // 2. Parse SMS template in customer/request language
                $parsedMessage = MessageTemplate::parseForBooking($booking, 'cancellation');

                // 3. Space out SMS in MessageQueue by 1 minute apart (1st SMS now, 2nd SMS now+1m, 3rd SMS now+2m...)
                $scheduledAt = $now->copy()->addMinutes($minuteOffset);

                if ($booking->customer && $booking->customer->phone) {
                    $formattedPhone = $booking->customer->phone;

                    $queueData = [
                        'barber_shop_id' => $booking->barber_shop_id,
                        'booking_id' => $booking->id,
                        'channel' => 'sms',
                        'phone_number' => $formattedPhone,
                        'message_content' => $parsedMessage,
                        'scheduled_at' => $scheduledAt,
                        'status' => 'pending',
                        'retry_count' => 0,
                    ];
                    if (Schema::hasColumn('message_queues', 'template_type')) {
                        $queueData['template_type'] = 'cancellation';
                    }
                    $queue = MessageQueue::create($queueData);

                    if ($minuteOffset === 0) {
                        $firstQueueId = $queue->id;
                    }
                }

                $minuteOffset++;
            }

            $item->update([
                'affected_count' => $affectedCount,
                'booking_ids' => $cancelledBookingIds,
            ]);

            // Dispatch immediate FCM trigger to Gateway Phone for the 1st SMS
            if ($firstQueueId) {
                try {
                    SendSmsToGatewayJob::dispatch($firstQueueId);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not dispatch SendSmsToGatewayJob for Queue #{$firstQueueId}: " . $e->getMessage());
                }
            }
        }

        return $item->fresh();
    }
}
