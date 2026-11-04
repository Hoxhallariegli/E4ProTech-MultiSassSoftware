<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menaxhimi i Rezervimit - {{ $shop->name ?? 'Salloni' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-3xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="p-6 bg-slate-800/80 border-b border-slate-700 text-center relative">
            <div class="w-16 h-16 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
                ✂️
            </div>
            <h1 class="text-xl font-bold text-white">{{ $shop->name ?? 'Salloni' }}</h1>
            <p class="text-xs text-slate-400 mt-1">Detajet e Takimit Juaj</p>
        </div>

        <div class="p-6 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl text-sm text-center">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-2xl text-sm text-center">
                    {{ session('error') }}
                </div>
            @endif

            @if($isExpired)
                <!-- Expired State -->
                <div class="bg-amber-500/10 border border-amber-500/30 p-5 rounded-2xl text-center space-y-2">
                    <div class="text-amber-400 text-3xl mb-1">⏳</div>
                    <h3 class="text-base font-semibold text-amber-300">Kjo lidhje ka skaduar</h3>
                    <p class="text-xs text-slate-400">
                        Takimi #{{ $booking->id }} ka përfunduar ose është kërkuar jashtë orarit të lejuar.
                    </p>
                </div>
            @else
                <!-- Active Booking Details -->
                <div class="bg-slate-900/50 border border-slate-700/60 rounded-2xl p-4 space-y-3">

                    <div class="flex items-center justify-between border-b border-slate-700/50 pb-3">
                        <span class="text-xs text-slate-400">Statusi i Takimit:</span>
                        @if($booking->status === 'confirmed')
                            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-semibold rounded-full">✅ i Konfirmuar</span>
                        @elseif($booking->status === 'cancelled')
                            <span class="px-3 py-1 bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold rounded-full">❌ i Anuluar</span>
                        @else
                            <span class="px-3 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-semibold rounded-full">⏳ Në Pritje</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Klienti:</span>
                        <span class="font-semibold text-white">{{ $booking->customer?->name ?? 'Klient' }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Shërbimi:</span>
                        <span class="font-semibold text-emerald-400">{{ $booking->service?->name ?? 'Shërbim Kryesor' }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Stafi / Berberi:</span>
                        <span class="font-semibold text-slate-200">{{ $booking->barber?->name ?? 'Staf' }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm border-t border-slate-700/50 pt-3">
                        <span class="text-slate-400">Data & Ora:</span>
                        <span class="font-bold text-white">
                            {{ $appointmentAt ? $appointmentAt->format('d/m/Y - H:i') : '10:00' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Çmimi:</span>
                        <span class="font-bold text-emerald-400">
                            {{ number_format((float) ($booking->service?->price ?? $booking->total_price ?? 0), 2) }} Lekë
                        </span>
                    </div>
                </div>

                <!-- Action Button for Customer -->
                @if($booking->status !== 'cancelled')
                    <form action="{{ route('public.booking.cancel', ['slug' => $shop->slug ?? 'salon', 'id' => $booking->id]) }}" method="POST" onsubmit="return confirm('Jeni i sigurt që dëshironi ta anuloni këtë takim?');">
                        @csrf
                        <button type="submit" class="w-full py-3 px-4 bg-rose-600/90 hover:bg-rose-600 text-white text-sm font-semibold rounded-2xl transition duration-200 shadow-lg shadow-rose-600/20">
                            ❌ Anulo Këtë Takim
                        </button>
                    </form>
                @endif
            @endif

            <!-- Navigation Button -->
            <div class="pt-2">
                <a href="{{ route('shop.landing', ['slug' => $shop->slug ?? 'salon']) }}" class="block w-full py-3 px-4 bg-slate-700/80 hover:bg-slate-700 text-slate-200 text-sm font-medium rounded-2xl text-center transition duration-200 border border-slate-600/50">
                    ✂️ Krijo një Takim të Ri
                </a>
            </div>

        </div>
    </div>

</body>
</html>
