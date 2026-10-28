<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsappSessionCloudController extends Controller
{
    /**
     * Save/Sync WhatsApp session JSON files from Render Cloud Gateway to Laravel DB
     */
    public function saveSession(Request $request): JsonResponse
    {
        $key = (string) $request->input('session_key');
        $files = $request->input('files');

        if (empty($key) || !is_array($files)) {
            return response()->json(['success' => false, 'error' => 'Invalid session payload'], 400);
        }

        WhatsappSession::updateOrCreate(
            ['session_key' => $key],
            ['session_data' => $files]
        );

        return response()->json(['success' => true, 'message' => "Session {$key} synced to cloud database!"]);
    }

    /**
     * Restore/Get saved session files for Render Cloud Gateway on server startup
     */
    public function loadSessions(Request $request): JsonResponse
    {
        $sessions = WhatsappSession::all();
        $data = [];

        foreach ($sessions as $s) {
            $data[$s->session_key] = $s->session_data;
        }

        return response()->json(['success' => true, 'sessions' => $data]);
    }

    /**
     * Delete a session from DB when user logs out from phone
     */
    public function deleteSession(Request $request): JsonResponse
    {
        $key = (string) $request->input('session_key');
        if ($key) {
            WhatsappSession::where('session_key', $key)->delete();
        }
        return response()->json(['success' => true, 'message' => "Session {$key} deleted from cloud DB."]);
    }
}
