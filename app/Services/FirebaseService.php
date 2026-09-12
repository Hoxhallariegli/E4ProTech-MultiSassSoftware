<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    public function sendNotification(string $title, string $body, string $topic = 'all'): bool
    {
        // Prioritizohet .env, nese jo merret nga databaza
        $enabled = env('FIREBASE_ENABLED', Setting::where('key', 'firebase_enabled')->value('value'));
        if (!$enabled) return false;

        $projectId = env('FIREBASE_PROJECT_ID', Setting::where('key', 'firebase_project_id')->value('value'));
        $credentialsJson = env('FIREBASE_CREDENTIALS', Setting::where('key', 'firebase_credentials')->value('value'));
        $credentials = json_decode($credentialsJson, true);

        if (!$projectId || !$credentials) {
            Log::warning('Firebase config is missing in both .env and Settings UI.');
            return false;
        }

        try {
            $token = $this->getAccessToken($credentials);
            if (!$token) return false;

            $message = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'title' => $title,
                    'body' => $body,
                ]
            ];

            // Nëse inputi 'topic' është në fakt një Token pajisjeje (i gjatë), përdor 'token'
            if (strlen($topic) > 50) {
                $message['token'] = $topic;
            } else {
                $message['topic'] = $topic;
            }

            $response = Http::withToken($token)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => $message
            ]);

            if (!$response->successful()) {
                Log::error('Firebase API Response: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gjeneron OAuth2 Token manualisht duke përdorur OpenSSL (pa pasur nevojë për librari të rënda).
     */
    protected function getAccessToken(array $credentials): ?string
    {
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

        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $credentials['private_key'], 'SHA256');
        $base64UrlSignature = $this->base64UrlEncode($signature);

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->json()['access_token'] ?? null;
    }

    protected function base64UrlEncode($data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
