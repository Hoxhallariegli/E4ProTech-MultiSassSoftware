<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            // Delete raw unencrypted legacy row for this session key if exists
            DB::table('whatsapp_sessions')->where('session_key', $key)->delete();

            $record = WhatsappSession::create([
                'session_key' => $key,
                'session_data' => $files,
            ]);

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
            $rawRows = DB::table('whatsapp_sessions')->get();
            $data = [];

            foreach ($rawRows as $row) {
                try {
                    $model = WhatsappSession::find($row->id);
                    $sessionData = $model ? $model->session_data : null;

                    if (is_array($sessionData)) {
                        $data[$row->session_key] = $sessionData;
                        continue;
                    }
                } catch (\Throwable $e) {
                    // Fallback for unencrypted raw JSON legacy row
                    $rawContent = $row->session_data;
                    if (is_string($rawContent)) {
                        $decoded = json_decode($rawContent, true);
                        if (is_array($decoded)) {
                            $data[$row->session_key] = $decoded;
                            continue;
                        }
                    }
                }
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
            DB::table('whatsapp_sessions')->where('session_key', $key)->delete();
            Log::info("🗑️ [CLOUD SESSION DELETED] Session {$key} deleted from DB");
        }
        return response()->json(['success' => true, 'message' => "Session {$key} deleted from cloud DB."]);
    }
}
