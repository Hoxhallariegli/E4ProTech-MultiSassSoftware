<?php

namespace App\Livewire\Admin;

use App\Models\RealtimeEvent;
use Livewire\Component;

class NotificationSettings extends Component
{
    public function toggleFirebase(int $eventId): void
    {
        $event = RealtimeEvent::findOrFail($eventId);
        $event->update(['firebase_enabled' => ! $event->firebase_enabled]);
    }

    public function render()
    {
        return view('livewire.admin.notification-settings', [
            'events' => RealtimeEvent::orderBy('event')->get(),
        ]);
    }
}
