<div class="space-y-8 p-4 sm:p-6">
    @php
        $isExpired = $shop ? $shop->is_expired : false;
        $currencySymbol = $shop ? $shop->resolved_currency : 'Lekë';
    @endphp

    @if($isExpired)
        <div class="bg-gradient-to-r from-red-500/10 via-amber-500/10 to-red-500/10 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm border border-red-200 dark:border-red-900/50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-500/20 text-red-600 dark:text-red-400 rounded-2xl text-2xl font-black shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-black text-red-900 dark:text-red-100 mb-1">
                        {{ __('admin.Subscription for your salon :name has expired!', ['name' => $shop?->name]) }}
                    </h3>
                    <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed mb-4">
                        {{ __('admin.Expired Notice') }}
                    </p>
                    <a href="{{ route('admin.subscription-renewals.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-black shadow-md transition">
                        {{ __('admin.Renew Subscription Now') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- TOP MODULE SHORTCUTS GRID (Permissions-driven with Blade Heroicons) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2">
                <x-heroicon-o-squares-2x2 class="w-4 h-4 text-[#7C5CFC]" />
                <span>Modulet &amp; Aksesi i Shpejtë</span>
            </h3>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">

            {{-- 1. Front Page Settings --}}
            @can('view_shop_front_page_settings')
                <a href="{{ route('admin.shop-front-page-settings.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-[#7C5CFC] transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-computer-desktop class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Front Page Settings') }}
                    </span>
                </a>
            @endcan

            {{-- 2. Bookings --}}
            @can('view_bookings')
                <a href="{{ route('admin.bookings.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-chart-bar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('bookings.Bookings') }}
                    </span>
                </a>
            @endcan

            {{-- 3. Barbers / Staff --}}
            @can('view_barbers')
                <a href="{{ route('admin.barbers.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-emerald-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-user class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('barbers.Barbers') }}
                    </span>
                </a>
            @endcan

            {{-- 4. Services --}}
            @can('view_services')
                <a href="{{ route('admin.services.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-amber-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-scissors class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('services.Services') }}
                    </span>
                </a>
            @endcan

            {{-- 5. Customers --}}
            @can('view_customers')
                <a href="{{ route('admin.customers.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-indigo-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-users class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('customers.Customers') }}
                    </span>
                </a>
            @endcan

            {{-- 6. Working Hours --}}
            @can('view_working_hours')
                <a href="{{ route('admin.working-hours.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-teal-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-clock class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('working-hours.WorkingHours') }}
                    </span>
                </a>
            @endcan

            {{-- 7. Payments --}}
            @can('view_payments')
                <a href="{{ route('admin.payments.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-green-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-banknotes class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('payments.Payments') }}
                    </span>
                </a>
            @endcan

            {{-- 8. Reports & Analytics --}}
            @can('view_reports')
                <a href="{{ route('admin.reports.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-rose-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-presentation-chart-line class="w-6 h-6 text-rose-600 dark:text-rose-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Reports & Analytics') }}
                    </span>
                </a>
            @endcan

            {{-- 9. Reviews --}}
            @can('view_reviews')
                <a href="{{ route('admin.reviews.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-yellow-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-star class="w-6 h-6 text-yellow-500 dark:text-yellow-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('reviews.Reviews') }}
                    </span>
                </a>
            @endcan

            {{-- 10. Message Queues --}}
            @can('view_message_queues')
                <a href="{{ route('admin.message-queues.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-cyan-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-bell class="w-6 h-6 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('message-queues.MessageQueues') }}
                    </span>
                </a>
            @endcan

            {{-- 11. Message Logs --}}
            @can('view_message_logs')
                <a href="{{ route('admin.message-logs.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-slate-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-document-text class="w-6 h-6 text-slate-600 dark:text-slate-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('message-logs.MessageLogs') }}
                    </span>
                </a>
            @endcan

            {{-- 12. Message Templates --}}
            @can('view_message_templates')
                <a href="{{ route('admin.message-templates.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-document-duplicate class="w-6 h-6 text-blue-500 dark:text-blue-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('message-templates.MessageTemplates') }}
                    </span>
                </a>
            @endcan

            {{-- 13. Event Settings --}}
            @can('view_event_settings')
                <a href="{{ route('admin.event-settings.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-amber-400 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-bolt class="w-6 h-6 text-amber-500 dark:text-amber-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('event-settings.EventSettings') }}
                    </span>
                </a>
            @endcan

            {{-- 14. Device Tokens / APK --}}
            @can('view_device_tokens')
                <a href="{{ route('admin.device-tokens.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-violet-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-device-phone-mobile class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('device-tokens.DeviceTokens') }}
                    </span>
                </a>
            @endcan

            {{-- 15. Notification Channels --}}
            @can('view_notification_channels')
                <a href="{{ route('admin.notification-channels.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-orange-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-rss class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('notification-channels.NotificationChannels') }}
                    </span>
                </a>
            @endcan

            {{-- 16. BarberShops (SaaS) --}}
            @can('view_barber_shops')
                <a href="{{ route('admin.barber-shops.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-600 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-building-office class="w-6 h-6 text-blue-700 dark:text-blue-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('barber-shops.BarberShops') }}
                    </span>
                </a>
            @endcan

            {{-- 17. Subscriptions --}}
            @can('view_subscriptions')
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-emerald-600 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-clipboard-document class="w-6 h-6 text-emerald-700 dark:text-emerald-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('subscriptions.Subscriptions') }}
                    </span>
                </a>
            @endcan

            {{-- 18. Subscription Renewals --}}
            @can('view_subscription_renewals')
                <a href="{{ route('admin.subscription-renewals.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-[#7C5CFC] transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-arrow-path class="w-6 h-6 text-[#7C5CFC]" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Subscription Renewals') }}
                    </span>
                </a>
            @endcan

            {{-- 19. Website Offer Requests --}}
            @can('view_website_offer_requests')
                <a href="{{ route('admin.website-offer-requests.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-indigo-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-globe-alt class="w-6 h-6 text-indigo-500" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Website Offer Requests') }}
                    </span>
                </a>
            @endcan

            {{-- 20. Plans --}}
            @can('view_plans')
                <a href="{{ route('admin.plans.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-teal-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-ticket class="w-6 h-6 text-teal-500" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('plans.Plans') }}
                    </span>
                </a>
            @endcan

            {{-- 21. Users --}}
            @can('view_users')
                <a href="{{ route('admin.users.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-blue-600 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-user-group class="w-6 h-6 text-blue-600" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Users') }}
                    </span>
                </a>
            @endcan

            {{-- 22. Roles --}}
            @can('view_roles')
                <a href="{{ route('admin.settings.roles.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-slate-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-key class="w-6 h-6 text-slate-500 dark:text-slate-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Roles') }}
                    </span>
                </a>
            @endcan

            {{-- 23. AI Assistant --}}
            @if(can('view_ai_assistant') || auth()->user()->is_global_admin)
                <a href="{{ route('admin.settings.ai-assistant') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-purple-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-cpu-chip class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        AI Assistant
                    </span>
                </a>
            @endif

            {{-- 24. Audit Trails --}}
            @can('view_audit_trails')
                <a href="{{ route('admin.settings.audit-trails.index') }}"
                   class="p-5 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-gray-500 transition-all flex flex-col items-center text-center space-y-3 group">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <x-heroicon-o-identification class="w-6 h-6 text-gray-500 dark:text-gray-300" />
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white line-clamp-1">
                        {{ __('admin.Audit Trails') }}
                    </span>
                </a>
            @endcan

        </div>
    </div>

    <!-- ONBOARDING & QUICK SETUP CARD (Hidden when 100% completed) -->
    @if($completedSteps < 4)
        <div class="card p-8 sm:p-10 bg-white dark:bg-gray-800 shadow-sm rounded-[2.5rem] border border-gray-100 dark:border-gray-700 space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-stone-100 dark:border-gray-700 pb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 mb-2">
                        <x-heroicon-o-bolt class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                        <span>{{ __('admin.Quick Onboarding Guide') }}</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ __('admin.Onboarding Setup') }}
                    </h2>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('admin.Setup Guide Subtitle') }}
                    </p>
                </div>

                <!-- Onboarding Progress Bar -->
                <div class="w-full sm:w-64 bg-stone-50 dark:bg-gray-900 p-4 rounded-2xl border border-stone-200 dark:border-gray-700 space-y-2">
                    <div class="flex justify-between items-center text-xs font-bold">
                        <span class="text-gray-700 dark:text-gray-300">{{ __('admin.Progress') }}:</span>
                        <span class="text-[#7C5CFC] font-extrabold">{{ $progressPercentage }}%</span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#7C5CFC] to-blue-600 transition-all duration-500 rounded-full"
                             style="width: {{ $progressPercentage }}%;"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 font-medium block text-right">{{ __('admin.:completed of 4 Steps Completed', ['completed' => $completedSteps]) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Step 1: Register Staff -->
                <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                                <x-heroicon-o-user class="w-5 h-5" />
                            </div>
                            @if($staffCount > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ __('admin.✓ :count Staff', ['count' => $staffCount]) }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300">
                                    {{ __('admin.Empty') }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">{{ __('admin.STEP 1') }}</span>
                        <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">{{ __('admin.Step 1 Title') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            {{ __('admin.Step 1 Desc') }}
                        </p>
                    </div>

                    <a href="{{ route('admin.barbers.index') }}"
                       class="w-full py-2.5 rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xs shadow-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span>{{ __('admin.Step 1 Button') }}</span>
                    </a>
                </div>

                <!-- Step 2: Register Services -->
                <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                                <x-heroicon-o-scissors class="w-5 h-5" />
                            </div>
                            @if($servicesCount > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ __('admin.✓ :count Services', ['count' => $servicesCount]) }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300">
                                    {{ __('admin.Empty') }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">{{ __('admin.STEP 2') }}</span>
                        <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">{{ __('admin.Step 2 Title') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            {{ __('admin.Step 2 Desc', ['currency' => $currencySymbol]) }}
                        </p>
                    </div>

                    <a href="{{ route('admin.services.index') }}"
                       class="w-full py-2.5 rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xs shadow-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span>{{ __('admin.Step 2 Button') }}</span>
                    </a>
                </div>

                <!-- Step 3: Download APK in NEW TAB -->
                <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                                <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
                            </div>
                            @if($hasFcmDevice)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ __('admin.✓ App Connected') }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-blue-800 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ __('admin.APK v:version', ['version' => $apkVersion]) }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">{{ __('admin.STEP 3') }}</span>
                        <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">{{ __('admin.Step 3 Title') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            {{ __('admin.Step 3 Desc') }}
                        </p>
                    </div>

                    <!-- OPEN APK DOWNLOAD IN NEW TAB -->
                    <a href="{{ route('app.download.apk') }}" target="_blank" rel="noopener"
                       class="w-full py-2.5 rounded-2xl bg-[#7C5CFC] hover:bg-[#6366F1] text-white font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                        <span>{{ __('admin.Step 3 Button') }}</span>
                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                    </a>
                </div>

                <!-- Step 4: SMS Gateway Setup -->
                <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                                <x-heroicon-o-bolt class="w-5 h-5" />
                            </div>
                            @if($smsGatewayOk)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ __('admin.✓ SMS Gateway Active') }}
                                </span>
                            @elseif($isSmsEnabled)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300">
                                    {{ __('admin.Select Device in APK') }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-stone-700 bg-stone-100 dark:bg-gray-700 dark:text-gray-300">
                                    {{ __('admin.Disabled') }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">{{ __('admin.STEP 4') }}</span>
                        <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">{{ __('admin.Step 4 Title') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                            {{ __('admin.Step 4 Desc') }}
                        </p>
                    </div>

                    <a href="{{ route('admin.settings.notifications') }}"
                       class="w-full py-2.5 rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xs shadow-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span>{{ __('admin.Step 4 Button') }}</span>
                    </a>
                </div>

            </div>
        </div>
    @endif
</div>
