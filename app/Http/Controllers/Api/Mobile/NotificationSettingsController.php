<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\RealtimeEvent;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NotificationSettingsController extends Controller
{
    public function index()
    {
        // Gjejmë të gjithë modulet unikë nga app/Domain ose nga realtime_events
        $events = RealtimeEvent::orderBy('event')->get();

        // Përgatisim modulet siç i kërkon UI (nga emri i eventit p.sh test-modules.created -> TestModule)
        $modules = [];
        $domainPath = app_path('Domain');
        if (is_dir($domainPath)) {
            foreach (scandir($domainPath) as $dir) {
                if ($dir !== '.' && $dir !== '..' && $dir !== 'Shared' && is_dir($domainPath . '/' . $dir)) {
                    $modules[] = [
                        'name' => $dir,
                        'enabled' => (bool) Setting::where('key', "notify_firebase_$dir")->value('value'),
                    ];
                }
            }
        }

        return response()->json([
            'events' => $events,
            'modules' => $modules,
        ]);
    }

    public function toggleModule(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
        ]);

        $module = $request->module;
        $currentValue = (bool) Setting::where('key', "notify_firebase_$module")->value('value');
        $newValue = !$currentValue;

        // 1. Update Global Setting
        Setting::updateOrCreate(
            ['key' => "notify_firebase_$module"],
            ['value' => $newValue]
        );

        // 2. Update RealtimeEvents for this module
        $prefix = Str::kebab(Str::plural($module));
        RealtimeEvent::where('event', 'like', "$prefix.%")
            ->update(['firebase_enabled' => $newValue]);

        // 3. Clear cache
        $events = RealtimeEvent::where('event', 'like', "$prefix.%")->get();
        foreach($events as $e) {
            Cache::forget("realtime_event:firebase:{$e->event}");
        }

        return response()->json([
            'success' => true,
            'enabled' => $newValue,
            'message' => "Notifications for $module updated!",
        ]);
    }

    public function toggleEvent(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
        ]);

        $event = RealtimeEvent::findOrFail($request->event_id);
        $event->update(['firebase_enabled' => !$event->firebase_enabled]);

        Cache::forget("realtime_event:firebase:{$event->event}");

        return response()->json([
            'success' => true,
            'enabled' => $event->firebase_enabled,
            'message' => "Event {$event->event} updated!",
        ]);
    }
}
