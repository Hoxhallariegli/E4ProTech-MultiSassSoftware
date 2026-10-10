<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') {{ $title ?? null }} - {{ config('app.name', 'Laravel') }}</title>
    @stack('scripts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Theme Loader
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || savedTheme === 'light') {
                document.documentElement.classList.toggle('dark', savedTheme === 'dark');
            } else if (prefersDark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        })();
    </script>

    @if(config('firebase_enabled') && config('firebase_web_config'))
        <script src="https://www.gstatic.com/firebasejs/9.1.1/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/9.1.1/firebase-messaging-compat.js"></script>
        <script>
            (function() {
                const isDebug = {{ config('firebase_debug', false) ? 'true' : 'false' }};
                const rawConfig = `{!! config('firebase_web_config') !!}`;

                try {
                    // Normalize JS Object to valid JSON
                    const jsonConfig = rawConfig.trim()
                        .replace(/([{,]\s*)([a-zA-Z0-9_]+)\s*:/g, '$1"$2":')
                        .replace(/'/g, '"')
                        .replace(/,\s*}/g, '}');

                    const firebaseConfig = JSON.parse(jsonConfig);
                    firebase.initializeApp(firebaseConfig);
                    const messaging = firebase.messaging();

                    if (isDebug) console.log('🔥 Firebase initialized successfully.');

                    // Handle messages when app is in foreground
                    messaging.onMessage((payload) => {
                        if (isDebug) console.log('📩 Message received (Foreground):', payload);

                        // 1. Shfaq Toast-in
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: payload.notification.title + ": " + payload.notification.body,
                                type: 'info'
                            }
                        }));

                        // 2. Shfaq Njoftimin e Windows-it (Surgical Force)
                        if (Notification.permission === "granted") {
                            navigator.serviceWorker.ready.then(registration => {
                                registration.showNotification(payload.notification.title, {
                                    body: payload.notification.body,
                                    icon: '/favicon.ico'
                                });
                            });
                        }
                    });

                    // Register Service Worker and Get Token
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/firebase-messaging-sw.js')
                            .then((registration) => {
                                console.log('⚙️ Service Worker registered for Web Push.');

                                const getWebFcmToken = () => {
                                    messaging.getToken({ serviceWorkerRegistration: registration })
                                        .then((token) => {
                                            if (token) {
                                                console.log('🔑 Web FCM Token:', token);

                                                const savedToken = sessionStorage.getItem('saved_fcm_token');
                                                if (savedToken === token) {
                                                    return;
                                                }

                                                // Direct HTTP fetch to save Web FCM token in database
                                                fetch('/api/mobile/device-tokens/save-web-token', {
                                                    method: 'POST',
                                                    credentials: 'same-origin',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'Accept': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                                    },
                                                    body: JSON.stringify({
                                                        fcm_token: token,
                                                        platform: 'web',
                                                        device_name: 'Web Browser (' + (navigator.userAgent.includes('Mobile') ? 'Mobile Chrome' : 'Desktop Browser') + ')',
                                                        user_id: @json(auth()->id()),
                                                        barber_shop_id: @json(auth()->user()?->barber_shop_id)
                                                    })
                                                }).then(r => r.json()).then(data => {
                                                    sessionStorage.setItem('saved_fcm_token', token);
                                                    console.log('✅ Web FCM Token saved to DB:', data);
                                                }).catch(err => {
                                                    console.error('❌ Error saving Web FCM Token to DB:', err);
                                                });

                                                if (typeof Livewire !== 'undefined') {
                                                    Livewire.dispatch('fcm-token-received', { token: token });
                                                }
                                            }
                                        })
                                        .catch((err) => console.error('❌ Web FCM Token Error:', err));
                                };

                                if (Notification.permission === 'granted') {
                                    console.log('🔔 Notification Permission: GRANTED');
                                    getWebFcmToken();
                                } else if (Notification.permission !== 'denied') {
                                    console.log('🔔 Notification Permission: Prompting user...');
                                    Notification.requestPermission().then((permission) => {
                                        console.log('🔔 Permission result:', permission);
                                        if (permission === 'granted') {
                                            getWebFcmToken();
                                        }
                                    });
                                } else {
                                    console.warn('🔔 Notification Permission: DENIED in browser settings');
                                }
                            }).catch(err => {
                                console.error('❌ SW Registration failed:', err);
                            });
                    }
                } catch (e) {
                    if (isDebug) console.error('💥 Firebase Boot Error:', e.message);
                }
            })();
        </script>
    @endif
</head>
<body>

<div
  x-data="{ userDropdownOpen: false, mobileSidebarOpen: false, desktopSidebarOpen: true }"
  x-bind:class="{ 'lg:pl-64': desktopSidebarOpen }"
  id="page-container"
  class="mx-auto flex min-h-dvh w-full min-w-80 flex-col bg-gray-100 lg:pl-64 dark:bg-gray-900 dark:text-gray-100"
>

  <nav
    x-bind:class="{
      '-translate-x-full': !mobileSidebarOpen,
      'translate-x-0': mobileSidebarOpen,
      'lg:-translate-x-full': !desktopSidebarOpen,
      'lg:translate-x-0': desktopSidebarOpen,
    }"
    id="page-sidebar"
    class="fixed top-0 bottom-0 left-0 z-50 flex h-full w-full -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-500 ease-out lg:w-64 lg:translate-x-0 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200"
    aria-label="Main Sidebar Navigation"
  >
    <div class="flex h-16 w-full flex-none items-center justify-between px-4 lg:justify-center dark:bg-gray-600/25">
      <x-a href="{{ route('dashboard') }}" class="group inline-flex items-center gap-2 text-lg font-bold tracking-wide text-gray-900 hover:text-gray-600 dark:text-gray-100 dark:hover:text-gray-300">
        <span>{{ config('app.name') }}</span>
      </x-a>
      <div class="lg:hidden">
        <button x-on:click="mobileSidebarOpen = false" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700">
          <x-heroicon-o-x-mark class="size-5" />
        </button>
      </div>
    </div>

    <div class="overflow-y-auto flex-1">
      <div class="w-full p-4">
        <nav class="space-y-1">
            @include('components.layouts.app.navigation')
        </nav>
      </div>
    </div>

    <!-- Bottom Sidebar Subscription Status Card -->
    @php
        $sidebarShop = auth()->user()?->barberShop;
        $sidebarPlanName = $sidebarShop?->active_plan_name ?? 'Trial (30 Ditë Falas)';
        $sidebarDaysLeft = $sidebarShop?->days_left ?? 30;
        $sidebarIsExpired = $sidebarShop?->is_expired ?? false;
    @endphp

    @if(auth()->check() && $sidebarShop)
        <div class="p-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
            <a href="{{ route('admin.subscriptions.index') }}"
               class="block p-3.5 rounded-2xl border transition group {{ $sidebarIsExpired ? 'bg-red-500/10 border-red-500/30 hover:border-red-500' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black uppercase tracking-wider {{ $sidebarIsExpired ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' }}">
                        {{ $sidebarIsExpired ? 'Abonimi Ka Skaduar' : 'Plani Aktiv' }}
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sidebarIsExpired ? 'bg-red-500/20 text-red-600 dark:text-red-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }}">
                        {{ $sidebarDaysLeft }} Ditë
                    </span>
                </div>
                <h5 class="text-xs font-black text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                    {{ $sidebarPlanName }}
                </h5>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium mt-0.5 truncate">
                    {{ $sidebarShop->name }}
                </p>
            </a>
        </div>
    @endif
  </nav>

  <header x-bind:class="{ 'lg:pl-64': true }" id="page-header" class="fixed top-0 right-0 left-0 z-30 flex h-16 flex-none items-center bg-white shadow-xs lg:pl-64 dark:bg-gray-800">
    <div class="mx-auto flex w-full max-w-10xl justify-between px-4 lg:px-8">
      <div class="flex items-center gap-2">
        <div class="lg:hidden">
          <button x-on:click="mobileSidebarOpen = !mobileSidebarOpen" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700">
            <x-heroicon-o-bars-3-center-left class="size-5" />
          </button>
        </div>
      </div>

      <div class="flex items-center gap-3">
          <button id="theme-toggle">
              <x-heroicon-o-sun id="theme-toggle-light" class="size-5 -mt-1 text-yellow-500" />
              <x-heroicon-o-moon id="theme-toggle-dark" class="size-5 -mt-1 text-gray-900 dark:text-white" />
          </button>

          <!-- Language Switcher -->
          <div x-data="{ open: false }" class="relative">
              <button @click="open = !open" class="flex items-center gap-1 text-sm font-bold uppercase focus:outline-none">
                  <span>{{ app()->getLocale() }}</span>
                  <x-heroicon-o-chevron-down class="size-3" />
              </button>
              <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-24 bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-100 dark:border-gray-600 z-50">
                  @php
                      $languages = ['en'];
                      if (File::exists(lang_path())) {
                          foreach (File::directories(lang_path()) as $dir) {
                              $lang = basename($dir);
                              if (strlen($lang) <= 5 && !in_array($lang, $languages)) $languages[] = $lang;
                          }
                          foreach (File::files(lang_path()) as $file) {
                              if ($file->getExtension() === 'json') {
                                  $lang = str_replace('.json', '', $file->getFilename());
                                  if (strlen($lang) <= 5 && !in_array($lang, $languages)) $languages[] = $lang;
                              }
                          }
                      }
                      sort($languages);
                  @endphp
                  @foreach($languages as $lang)
                    <a href="{{ route('language.switch', $lang) }}" class="block px-4 py-2 text-xs font-bold uppercase hover:bg-gray-100 dark:hover:bg-gray-600 {{ $loop->first ? 'rounded-t-lg' : '' }} {{ $loop->last ? 'rounded-b-lg' : '' }}">{{ $lang }}</a>
                  @endforeach
              </div>
          </div>

          <livewire:admin.shop-switcher/>
          <livewire:admin.notifications-menu/>
          <livewire:admin.users.user-menu/>
      </div>
    </div>
  </header>

  <main id="page-content" class="flex max-w-full flex-auto flex-col pt-16">
    @php
        $globalExpired = false;
        $globalShopName = '';
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->is_global_admin) {
                $globalShop = $user->barberShop;
                if ($globalShop && $globalShop->is_expired) {
                    $globalExpired = true;
                    $globalShopName = $globalShop->name;
                }
            }
        }
    @endphp

    @if($globalExpired)
        <!-- Full-Screen Unclosable Modal Lock Overlay -->
        <div class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4 sm:p-6 antialiased selection:bg-red-500 selection:text-white">
            <div class="w-full max-w-lg bg-white dark:bg-gray-900 border border-red-500/40 rounded-[2.5rem] shadow-2xl p-8 sm:p-10 text-center relative overflow-hidden">

                <!-- Top Warning Icon Badge -->
                <div class="w-20 h-20 rounded-3xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-6 text-4xl shadow-xl">
                    ⚠️
                </div>

                <!-- Modal Title -->
                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight mb-3">
                    Abonimi Juaj Ka Skaduar!
                </h2>

                <!-- Modal Description -->
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-medium leading-relaxed max-w-md mx-auto mb-8">
                    Periudha e provës 30 ditore ose abonimi aktiv për sallonin tuaj <strong class="text-red-600 dark:text-red-400 font-bold">"{{ $globalShopName }}"</strong> ka përfunduar (0 Ditë Mbetura).<br><br>
                    Për të vazhduar përdorimin e panelit, qasjen në modulet tuaja dhe pranimin e rezervimeve online, ju lutemi renovoni abonimin tuaj.
                </p>

                <!-- Renewal CTA Button -->
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="w-full py-4 rounded-2xl font-black text-sm text-white bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 shadow-xl shadow-red-500/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>Renovo Abonimin Tani ↗</span>
                </a>

                <!-- Notice -->
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-4 font-semibold">
                    Keni pyetje? Kontaktoni mbështetjen e E4ProTech Engine.
                </p>
            </div>
        </div>
    @endif

    <div class="mx-auto w-full max-w-10xl p-4 lg:p-8">
        {{ $slot ?? '' }}
    </div>
  </main>

  <footer id="page-footer" class="flex flex-none items-center bg-white dark:bg-gray-800/50">
    <div class="mx-auto flex w-full max-w-10xl flex-col px-4 text-center text-sm md:flex-row md:justify-between md:text-left lg:px-8">
      <div class="pt-4 pb-1 md:pb-4">
        {{ __('Copyright') }} &copy; {{ date('Y') }} {{ config('app.name') }}
      </div>
      <div class="inline-flex items-center justify-center pt-1 pb-4 md:pt-4">
        <span>
            {{ __('Built by') }} <a href="https://e4protech.com" target="_blank" class="font-medium text-blue-600 hover:text-blue-400 dark:text-blue-400 dark:hover:text-blue-300">Hoxhallari Egli</a>
        </span>
      </div>
    </div>
  </footer>
</div>

<script>
    @if(session()->has('success'))
        window.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: "{{ session('success') }}", type: 'success' } }));
        });
    @endif

    @if(session()->has('error'))
        window.addEventListener('DOMContentLoaded', () => {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: "{{ session('error') }}", type: 'error' } }));
        });
    @endif
</script>

</body>
</html>
