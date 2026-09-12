<div class="p-6">
    <h1 class="text-xl font-bold mb-1">Njoftimet Firebase — sipas Event-it</h1>
    <p class="text-sm text-gray-500 mb-4">
        Reverb (realtime brenda APK) mbetet gjithmonë aktiv për çdo view. Ky switch kontrollon
        vetëm nëse dërgohet edhe push notification (FCM) për një aksion specifik (created/updated/deleted).
    </p>

    <table class="w-full border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-4 py-2">Event</th>
                <th class="text-left px-4 py-2">Label</th>
                <th class="text-left px-4 py-2">Firebase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($events as $event)
                <tr class="border-t">
                    <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $event->event }}</td>
                    <td class="px-4 py-2">{{ $event->label }}</td>
                    <td class="px-4 py-2">
                        <button
                            wire:click="toggleFirebase({{ $event->id }})"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 rounded-full text-sm font-semibold transition
                                {{ $event->firebase_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}"
                        >
                            {{ $event->firebase_enabled ? 'PO' : 'JO' }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                        Ende s'ka evente të regjistruara. Krijo një view me <code>php artisan new:view</code>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
