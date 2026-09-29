<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    public function sendNotification(string $title, string $body, string $topic = 'all', array $customData = []): bool
    {
        $dbEnabled = Setting::where('key', 'firebase_enabled')->value('value');
        $enabled = filter_var(env('FIREBASE_ENABLED', $dbEnabled), FILTER_VALIDATE_BOOLEAN) || filter_var($dbEnabled, FILTER_VALIDATE_BOOLEAN);

        if (!$enabled) {
            Log::warning('Firebase notification skipped: Firebase is disabled in Settings.');
            return false;
        }

        $projectId = env('FIREBASE_PROJECT_ID') ?: Setting::where('key', 'firebase_project_id')->value('value');
        $credentialsJson = env('FIREBASE_CREDENTIALS') ?: Setting::where('key', 'firebase_credentials')->value('value');
        $credentials = is_string($credentialsJson) ? json_decode($credentialsJson, true) : null;

        if (!$projectId || !$credentials || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            Log::warning('Firebase config missing or invalid credentials JSON in Settings UI or .env.');
            return false;
        }

        try {
            $token = $this->getAccessToken($credentials);
            if (!$token) {
                Log::error('Firebase OAuth2 token generation failed.');
                return false;
            }

            $payloadData = array_merge([
                'title' => $title,
                'body' => $body,
            ], $customData);

            $message = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $payloadData),
            ];

            if (strlen($topic) > 30) {
                $message['token'] = $topic;
            } else {
                $message['topic'] = $topic;
            }

            $response = Http::withToken($token)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => $message
            ]);

            if (!$response->successful()) {
                Log::error('Firebase API Error Response: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Firebase Notification Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendToShop(int $shopId, string $title, string $body, array $customData = []): int
    {
        // 1. Send via FCM Topic dedicated to shop "shop_{$shopId}"
        $topic = "shop_{$shopId}";
        $this->sendNotification($title, $body, $topic, $customData);

        // 2. Send directly to all registered FCM device tokens for this specific shop
        $tokens = DeviceToken::where('barber_shop_id', $shopId)
            ->pluck('fcm_token')
            ->filter()
            ->unique();

        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendNotification($title, $body, $token, $customData)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    public function sendToAllDevices(string $title, string $body): int
    {
        $tokens = DeviceToken::pluck('fcm_token')->filter()->unique();
        $successCount = 0;

        foreach ($tokens as $token) {
            if ($this->sendNotification($title, $body, $token)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    protected function getAccessToken(array $credentials): ?string
    {
        try {
            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $now = time();
            $payload = json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]);

            $base64UrlHeader = $this->base64UrlEncode($header);
            $base64UrlPayload = $this->base64UrlEncode($payload);

            $key = $credentials['private_key'];
            openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $key, 'SHA256');
            $base64UrlSignature = $this->base64UrlEncode($signature);

            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $response->json()['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Firebase AccessToken Error: ' . $e->getMessage());
            return null;
        }
    }

    protected function base64UrlEncode($data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
