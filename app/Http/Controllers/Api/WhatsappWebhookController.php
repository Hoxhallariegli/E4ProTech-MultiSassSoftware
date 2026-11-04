<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // Verify Secret X-API-KEY Header
        $apiKey = $request->header('X-API-KEY') ?: $request->input('api_key');
        $expectedKey = config('services.whatsapp.secret', 'E4ProTech_Secret_Key_2026_Secure');

        if ($apiKey !== $expectedKey) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $shopId = (int) ($request->input('shop_id') ?: 1);
        $phone = trim((string) $request->input('phone'));
        $text = trim((string) $request->input('text'));

        Log::info("💬 [WhatsApp Webhook] Incoming message logged for Shop #{$shopId} from {$phone}: '{$text}'");

        return response()->json([
            'success' => true,
            'message' => 'Notification webhook logged successfully.'
        ]);
    }
}
