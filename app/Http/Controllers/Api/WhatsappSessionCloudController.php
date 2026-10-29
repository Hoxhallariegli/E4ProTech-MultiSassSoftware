<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WhatsappSessionCloudController extends Controller
{
    private function validateApiKey(Request $request): bool
    {
        $key = $request->header('X-API-KEY') ?? $request->input('api_key');
        $secret = config('services.whatsapp.secret', 'E4ProTech_Secret_Key_2026_Secure');
        return $key === $secret;
    }

    /**
     * Save/Sync WhatsApp session JSON files from Render Cloud Gateway to Laravel DB
     */
    public function saveSession(Request $request): JsonResponse
    {
        if (!$this->validateApiKey($request)) {
            Log::warning("❌ [CLOUD SESSION REJECTED] Invalid API Key received");
            return response()->json(['success' => false, 'error' => 'Unauthorized API Key'], 401);
        }

        $key = (string) $request->input('session_key');
        $files = $request->input('files');

        if (empty($key) || !is_array($files)) {
            Log::warning("❌ [CLOUD SESSION INVALID] Empty session_key or files payload for {$key}");
            return response()->json(['success' => false, 'error' => 'Invalid session payload'], 400);
        }

        try {
            $record = WhatsappSession::updateOrCreate(
                ['session_key' => $key],
                ['session_data' => $files]
            );

            Log::info("✅ [CLOUD SESSION SAVED] Session {$key} saved in DB with " . count($files) . " files!");
            return response()->json(['success' => true, 'message' => "Session {$key} synced to cloud database!"]);
        } catch (\Throwable $e) {
            Log::error("❌ [CLOUD SESSION DB ERROR] " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore/Get saved session files for Render Cloud Gateway on server startup
     */
    public function loadSessions(Request $request): JsonResponse
    {
        if (!$this->validateApiKey($request)) {
            Log::warning("❌ [CLOUD SESSION LOAD REJECTED] Invalid API Key");
            return response()->json(['success' => false, 'error' => 'Unauthorized API Key'], 401);
        }

        try {
            $sessions = WhatsappSession::all();
            $data = [];

            foreach ($sessions as $s) {
                $data[$s->session_key] = $s->session_data;
            }

            Log::info("📦 [CLOUD SESSIONS LOADED] Loaded " . count($data) . " sessions from DB");
            return response()->json(['success' => true, 'sessions' => $data]);
        } catch (\Throwable $e) {
            Log::error("❌ [CLOUD SESSIONS LOAD ERROR] " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a session from DB when user logs out from phone
     */
    public function deleteSession(Request $request): JsonResponse
    {
        if (!$this->validateApiKey($request)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized API Key'], 401);
        }

        $key = (string) $request->input('session_key');
        if ($key) {
            WhatsappSession::where('session_key', $key)->delete();
            Log::info("🗑️ [CLOUD SESSION DELETED] Session {$key} deleted from DB");
        }
        return response()->json(['success' => true, 'message' => "Session {$key} deleted from cloud DB."]);
    }
}
