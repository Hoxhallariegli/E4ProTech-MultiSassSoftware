<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('mobile.{shopId}.{resource}', function (\App\Models\User $user, $shopId) {
    if ($user->hasRole('admin')) return true;

    // Lejo aksesin nese eshte owner ose ne pivot table (barber_shop_user)
    return (int) $user->barber_shop_id === (int) $shopId
        || $user->barberShops()->where('barber_shops.id', $shopId)->exists();
}, ['guards' => ['sanctum']]);

Broadcast::channel('mobile.{shopId}.barber-shops', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_barber_shops'); });

Broadcast::channel('mobile.{shopId}.plans', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_plans'); });

Broadcast::channel('mobile.{shopId}.subscriptions', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_subscriptions'); });

Broadcast::channel('mobile.{shopId}.barbers', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_barbers'); });

Broadcast::channel('mobile.{shopId}.services', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_services'); });

Broadcast::channel('mobile.{shopId}.working-hours', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_working_hours'); });

Broadcast::channel('mobile.{shopId}.customers', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_customers'); });

Broadcast::channel('mobile.{shopId}.notification-channels', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_notification_channels'); });

Broadcast::channel('mobile.{shopId}.event-settings', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_event_settings'); });

Broadcast::channel('mobile.{shopId}.message-templates', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_message_templates'); });

Broadcast::channel('mobile.{shopId}.bookings', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_bookings'); });

Broadcast::channel('mobile.{shopId}.payments', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_payments'); });

Broadcast::channel('mobile.{shopId}.message-queues', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_message_queues'); });

Broadcast::channel('mobile.{shopId}.message-logs', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_message_logs'); });

Broadcast::channel('mobile.{shopId}.device-tokens', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_device_tokens'); });

Broadcast::channel('mobile.{shopId}.reviews', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId && $user->can('view_reviews'); });

Broadcast::channel('mobile.{shopId}.test-modules', function (\App\Models\User $user, $shopId) { if ($user->hasRole('admin')) return true; return (int) $user->barber_shop_id === (int) $shopId; });
