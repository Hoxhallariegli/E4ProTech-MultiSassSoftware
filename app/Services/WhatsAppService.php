<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $gatewayUrl;
    protected string $secretKey;

    public function __construct()
    {
        $this->gatewayUrl = config('services.whatsapp.gateway_url', 'http://127.0.0.1:3000/api/whatsapp/send');
        $this->secretKey = config('services.whatsapp.secret', 'E4ProTech_Secret_Key_2026_Secure');
    }

    /**
     * Send a WhatsApp message via the Baileys Gateway microservice (Multi-Shop & Multi-Barber Aware).
     */
    public function send(string $phone, string $message, int|string|null $shopId = null, int|string|null $barberId = null, int|string|null $customerId = null, ?string $templateType = null): bool
    {
        if (empty($phone) || empty($message)) {
            return false;
        }

        try {
            $payload = [
                'phone' => $phone,
                'message' => $message,
                'shop_id' => (string) ($shopId ?: '1'),
            ];

            if ($barberId) {
                $payload['barber_id'] = (string) $barberId;
            }

            // Retry up to 2 times with 25s timeout to handle Render Free Tier wake-up delay
            $response = Http::withHeaders([
                'X-API-KEY' => $this->secretKey,
            ])->retry(2, 1000)->timeout(25)->post($this->gatewayUrl, $payload);

            $isSuccess = $response->successful() && $response->json('success') === true;

            // Log message in MessageLog database table for audit & analytics
            try {
                \App\Models\MessageLog::create([
                    'barber_shop_id' => $shopId ?: 1,
                    'customer_id' => $customerId ?: 0,
                    'channel' => 'whatsapp',
                    'template_type' => $templateType ?: 'confirmation',
                    'message' => $message,
                    'status' => $isSuccess ? 'sent' : 'failed',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not create MessageLog audit record: " . $e->getMessage());
            }

            if ($isSuccess) {
                Log::info("WhatsApp message sent successfully to {$phone} for Shop #{$shopId} (Barber #{$barberId})");
                return true;
            }

            Log::warning("WhatsApp send failed for {$phone} (Shop #{$shopId}): " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp Gateway connection error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if WhatsApp Gateway is online and connected for a given shop or barber.
     */
    public function isConnected(int|string|null $shopId = null, int|string|null $barberId = null): bool
    {
        try {
            $key = $barberId ? "barber_{$barberId}" : "shop_" . ($shopId ?: '1');
            $statusUrl = str_replace('/send', '/status', $this->gatewayUrl) . '?session_key=' . $key;
            $response = Http::withHeaders([
                'X-API-KEY' => $this->secretKey,
            ])->timeout(8)->get($statusUrl);

            return $response->successful() && $response->json('connected') === true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
