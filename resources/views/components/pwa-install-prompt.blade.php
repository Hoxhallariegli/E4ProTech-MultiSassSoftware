<!-- PWA Install & Push Notification Banner Component -->
<div id="pwa-install-banner" x-data="pwaInstaller()" x-show="showBanner" x-cloak
     class="fixed bottom-4 left-3 right-3 sm:left-auto sm:right-6 sm:max-w-md z-50 bg-[#1A1D20]/95 backdrop-blur-lg text-white p-4 rounded-3xl border border-white/20 shadow-2xl transition-all duration-300 transform">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            @if(!empty($shop->logo))
                <img src="{{ asset(ltrim($shop->logo, '/')) }}" alt="{{ $shop->name }}" class="w-12 h-12 rounded-2xl object-cover border border-white/20 shrink-0">
            @else
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-teal-500 font-black text-xl flex items-center justify-center text-white shrink-0">
                    📲
                </div>
            @endif
            <div>
                <h4 class="font-extrabold text-sm text-white flex items-center gap-1.5">
                    <span>{{ app()->getLocale() === 'en' ? 'Install App' : 'Instalo Aplikacionin' }}</span>
                </h4>
                <p class="text-[11px] text-stone-300 leading-snug font-medium mt-0.5">
                    {{ app()->getLocale() === 'en'
                        ? 'Add to home screen for 1-tap bookings & instant notifications!'
                        : 'Shtoje në ekran për rezervime më të shpejta me 1 klikim & njoftime!' }}
                </p>
            </div>
        </div>

        <button @click="dismissBanner()" class="text-stone-400 hover:text-white text-lg font-black p-1 cursor-pointer">
            ✕
        </button>
    </div>

    <!-- Actions -->
    <div class="mt-3 pt-3 border-t border-white/10 flex items-center gap-2">
        <button @click="installApp()"
                class="flex-1 py-2.5 px-4 rounded-xl text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
            <span>📲</span>
            <span>{{ app()->getLocale() === 'en' ? 'Install Now' : 'Instalo Tani' }}</span>
        </button>

        <button @click="requestNotification()"
                class="py-2.5 px-3 rounded-xl text-xs font-bold text-stone-200 bg-white/10 hover:bg-white/20 transition active:scale-95 flex items-center justify-center gap-1 cursor-pointer">
            <span>🔔</span>
            <span>{{ app()->getLocale() === 'en' ? 'Notifications' : 'Njoftimet' }}</span>
        </button>
    </div>

    <!-- iOS Instructions Popup Modal -->
    <div x-show="showIosModal" x-cloak @click.away="showIosModal = false"
         class="mt-3 p-3 rounded-2xl bg-white/10 border border-white/20 text-xs text-stone-200 space-y-1.5">
        <p class="font-bold text-amber-300 text-[11px]">iPhone / iPad (Safari):</p>
        <p class="text-[11px]">1. Klikoni butonin <strong>Share ⎋</strong> te shfletuesi Safari</p>
        <p class="text-[11px]">2. Zgjidhni <strong>"Add to Home Screen ➕"</strong></p>
    </div>
</div>

<script>
    function pwaInstaller() {
        return {
            showBanner: false,
            showIosModal: false,
            deferredPrompt: null,

            init() {
                // Check if already installed
                if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                    return;
                }

                if (localStorage.getItem('pwa_banner_dismissed_until')) {
                    const dismissedUntil = parseInt(localStorage.getItem('pwa_banner_dismissed_until'));
                    if (Date.now() < dismissedUntil) return;
                }

                // Register Service Worker
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('/sw.js').catch(function(e){});
                }

                // Listen for native install prompt
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.showBanner = true;
                });

                // Show for iOS devices after 3 seconds
                const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                if (isIos && !window.navigator.standalone) {
                    setTimeout(() => {
                        this.showBanner = true;
                    }, 3000);
                } else if (!this.deferredPrompt) {
                    // Show banner after 4 seconds on all browsers
                    setTimeout(() => {
                        this.showBanner = true;
                    }, 4000);
                }
            },

            installApp() {
                if (this.deferredPrompt) {
                    this.deferredPrompt.prompt();
                    this.deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            this.showBanner = false;
                        }
                        this.deferredPrompt = null;
                    });
                } else {
                    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                    if (isIos) {
                        this.showIosModal = !this.showIosModal;
                    } else {
                        alert(
                            "Për ta instaluar këtë Aplikacion:\n1. Klikoni menyne e shfletuesit (3 pikat ⋮)\n2. Zgjidhni 'Add to Home Screen' ose 'Install App'"
                        );
                    }
                }
            },

            requestNotification() {
                if ('Notification' in window) {
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            alert("✅ Njoftimet u aktivizuan me sukses!");
                        } else {
                            alert("Ju lutemi lejoni njoftimet nga cilësimet e shfletuesit.");
                        }
                    });
                }
            },

            dismissBanner() {
                this.showBanner = false;
                // Dismiss banner for 3 days
                localStorage.setItem('pwa_banner_dismissed_until', Date.now() + (3 * 24 * 60 * 60 * 1000));
            }
        }
    }
</script>
