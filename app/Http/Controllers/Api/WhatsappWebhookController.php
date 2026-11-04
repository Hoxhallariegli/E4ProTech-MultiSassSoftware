<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MessageLog;
use App\Models\AuditTrail;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify Secret X-API-KEY Header
        $apiKey = $request->header('X-API-KEY') ?: $request->input('api_key');
        $expectedKey = config('services.whatsapp.secret', 'E4ProTech_Secret_Key_2026_Secure');

        if ($apiKey !== $expectedKey) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $shopId = (int) ($request->input('shop_id') ?: 1);
        $phone = trim((string) $request->input('phone'));
        $buttonId = (string) $request->input('button_id');
        $text = strtolower(trim((string) $request->input('text')));

        if (empty($phone)) {
            return response()->json(['success' => false, 'message' => 'No phone provided'], 400);
        }

        Log::info("💬 [WhatsApp Webhook] Incoming message from {$phone} (Shop #{$shopId}): Button='{$buttonId}', Text='{$text}'");

        // 2. Resolve Intent (Confirm vs Cancel)
        $action = null;

        $confirmKeywords = ['po', 'konfirmo', 'konfirmoj', '1', 'yes', 'confirm', '✅ po, do të vij', 'po, do te vij', 'vij', 'do vij', 'do te vij', 'ok', 'dakor', 'dakord', '1️⃣'];
        $cancelKeywords = ['anulo', 'anuloj', 'jo', '2', 'no', 'cancel', '❌ anulo takimin', 'anulo takimin', 'sdo vij', 'nuk vij', 's\'mund te vij', '2️⃣', 'nuk mundem'];

        if (str_contains($buttonId, 'confirm_')) {
            $action = 'confirm';
        } elseif (str_contains($buttonId, 'cancel_')) {
            $action = 'cancel';
        } else {
            foreach ($confirmKeywords as $cw) {
                if ($text === $cw || str_contains(" {$text} ", " {$cw} ")) {
                    $action = 'confirm';
                    break;
                }
            }
            if (!$action) {
                foreach ($cancelKeywords as $cw) {
                    if ($text === $cw || str_contains(" {$text} ", " {$cw} ")) {
                        $action = 'cancel';
                        break;
                    }
                }
            }
        }

        if (!$action) {
            return response()->json(['success' => true, 'message' => 'Message received, no interactive action needed.']);
        }

        // 3. Resolve Target Booking ID
        $bookingId = null;
        if (preg_match('/(confirm|cancel)_(\d+)/', $buttonId, $matches)) {
            $bookingId = (int) $matches[2];
        }

        $booking = null;
        if ($bookingId) {
            $booking = Booking::withoutGlobalScope('barber_shop_access')
                ->where('id', $bookingId)
                ->where('barber_shop_id', $shopId)
                ->first();
        }

        $digitsOnly = preg_replace('/[^\d]/', '', $phone);
        $last7 = strlen($digitsOnly) >= 7 ? substr($digitsOnly, -7) : $digitsOnly;

        // Strategy A: Check MessageLog for the EXACT booking ID that was last sent to this customer on WhatsApp
        if (!$booking) {
            $latestLog = MessageLog::whereNotNull('booking_id')
                ->where(function ($q) use ($phone, $last7) {
                    $q->where('phone_number', 'like', "%{$phone}%")
                      ->orWhere('phone_number', 'like', "%{$last7}%")
                      ->orWhereHas('customer', function ($cq) use ($last7) {
                          $cq->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?", ["%{$last7}%"]);
                      });
                })
                ->latest('id')
                ->first();

            if ($latestLog && $latestLog->booking_id) {
                $targetBooking = Booking::withoutGlobalScope('barber_shop_access')->find($latestLog->booking_id);
                if ($targetBooking && $targetBooking->status !== 'cancelled') {
                    $booking = $targetBooking;
                    Log::info("🎯 [WhatsApp Webhook] Resolved exact Booking #{$booking->id} from MessageLog ID #{$latestLog->id}");
                }
            }
        }

        // Strategy B: Fallback to latest non-cancelled booking for this customer's phone
        if (!$booking) {
            $customerIds = Customer::withoutGlobalScope('barber_shop_access')
                ->where(function ($q) use ($shopId) {
                    if ($shopId) $q->where('barber_shop_id', $shopId);
                })
                ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?", ["%{$last7}%"])
                ->pluck('id');

            $booking = Booking::withoutGlobalScope('barber_shop_access')
                ->where(function ($q) use ($shopId) {
                    if ($shopId) $q->where('barber_shop_id', $shopId);
                })
                ->where(function ($q) use ($customerIds, $last7) {
                    if ($customerIds->isNotEmpty()) {
                        $q->whereIn('customer_id', $customerIds);
                    }
                    $q->orWhereHas('customer', function ($cq) use ($last7) {
                        $cq->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?", ["%{$last7}%"]);
                    });
                })
                ->where('status', '!=', 'cancelled')
                ->where('appointment_at', '>=', now()->subHours(24))
                ->latest('id')
                ->first();
        }

        if (!$booking) {
            Log::warning("⚠️ [WhatsApp Webhook] No matching active booking found for phone {$phone} in Shop #{$shopId}");
            return response()->json(['success' => true, 'message' => 'No active booking found for customer.']);
        }

        $shop = $booking->barberShop;
        $shopName = $shop?->name ?? 'Salloni';

        // 4. Process Action
        if ($action === 'confirm') {
            if ($booking->status !== 'confirmed' && $booking->status !== 'completed') {
                $booking->update(['status' => 'confirmed']);
                AuditTrail::logCustom($booking, 'update', 'Bookings', "Konfirmuar nga klienti në WhatsApp (#{$booking->id})");

                Log::info("✅ [WhatsApp Webhook] Booking #{$booking->id} confirmed via WhatsApp reply!");

                // Send Confirmation Ack Message to Customer
                app(WhatsAppService::class)->send(
                    $phone,
                    "✅ Faleminderit! Takimi juaj #{$booking->id} u konfirmua me sukses. Ju mirëpresim te {$shopName}!",
                    $shopId,
                    $booking->barber_id,
                    $booking->customer_id,
                    'confirmation',
                    $booking->id
                );
            }
        } elseif ($action === 'cancel') {
            if ($booking->status !== 'cancelled') {
                $booking->update(['status' => 'cancelled']);
                AuditTrail::logCustom($booking, 'update', 'Bookings', "Anuluar nga klienti në WhatsApp (#{$booking->id})");

                Log::info("❌ [WhatsApp Webhook] Booking #{$booking->id} cancelled via WhatsApp reply!");

                // Send Cancellation Ack Message to Customer
                app(WhatsAppService::class)->send(
                    $phone,
                    "❌ Takimi juaj #{$booking->id} u anulua me sukses te {$shopName}. Faleminderit që na njoftuat!",
                    $shopId,
                    $booking->barber_id,
                    $booking->customer_id,
                    'cancellation',
                    $booking->id
                );
            }
        }

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'action' => $action,
        ]);
    }
}
