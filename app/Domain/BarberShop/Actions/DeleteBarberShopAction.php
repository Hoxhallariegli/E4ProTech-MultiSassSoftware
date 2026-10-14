<?php

namespace App\Domain\BarberShop\Actions;

use App\Models\BarberShop;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteBarberShopAction
{
    public function execute(BarberShop $model): bool
    {
        return DB::transaction(function () use ($model) {
            $shopId = $model->id;

            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } catch (\Throwable $e) {}

            try {
                // 1. Delete Shop Front Page Landing Settings
                if (\Illuminate\Support\Facades\Schema::hasTable('shop_front_page_settings')) {
                    DB::table('shop_front_page_settings')->where('barber_shop_id', $shopId)->delete();
                }

                // 2. Delete Pivot & Permission Relationships
                if (\Illuminate\Support\Facades\Schema::hasTable('barber_shop_user')) {
                    DB::table('barber_shop_user')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('model_has_permissions')) {
                    DB::table('model_has_permissions')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                    DB::table('roles')->where('barber_shop_id', $shopId)->delete();
                }

                // 3. Delete Notification Channels & Event Settings
                if (\Illuminate\Support\Facades\Schema::hasTable('notification_channels')) {
                    DB::table('notification_channels')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('event_settings')) {
                    DB::table('event_settings')->where('barber_shop_id', $shopId)->delete();
                }

                // 4. Delete Bookings, Payments, Reviews, Reports
                if (\Illuminate\Support\Facades\Schema::hasTable('reports')) {
                    DB::table('reports')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('reviews')) {
                    DB::table('reviews')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                    DB::table('payments')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('bookings')) {
                    DB::table('bookings')->where('barber_shop_id', $shopId)->delete();
                }

                // 5. Delete Subscriptions & Renewals
                if (\Illuminate\Support\Facades\Schema::hasTable('subscription_renewals')) {
                    DB::table('subscription_renewals')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('subscriptions')) {
                    DB::table('subscriptions')->where('barber_shop_id', $shopId)->delete();
                }

                // 6. Delete SMS Queue, Logs, Templates, Device Tokens
                if (\Illuminate\Support\Facades\Schema::hasTable('message_queues')) {
                    DB::table('message_queues')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('message_logs')) {
                    DB::table('message_logs')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('message_templates')) {
                    DB::table('message_templates')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('device_tokens')) {
                    DB::table('device_tokens')->where('barber_shop_id', $shopId)->delete();
                }

                // 7. Delete Working Hours & Services
                if (\Illuminate\Support\Facades\Schema::hasTable('barbers')) {
                    $barberIds = DB::table('barbers')->where('barber_shop_id', $shopId)->pluck('id')->toArray();
                    if (!empty($barberIds) && \Illuminate\Support\Facades\Schema::hasTable('working_hours')) {
                        DB::table('working_hours')->whereIn('barber_id', $barberIds)->delete();
                    }
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('services')) {
                    DB::table('services')->where('barber_shop_id', $shopId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('barbers')) {
                    DB::table('barbers')->where('barber_shop_id', $shopId)->delete();
                }

                // 8. Delete Customers
                if (\Illuminate\Support\Facades\Schema::hasTable('customers')) {
                    DB::table('customers')->where('barber_shop_id', $shopId)->delete();
                }

                // 9. Unlink and Delete Non-Global-Admin Shop Users
                if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                    $globalAdminIds = [];
                    if (\Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
                        $globalAdminIds = DB::table('model_has_roles')
                            ->where('barber_shop_id', 0)
                            ->pluck('model_id')
                            ->toArray();
                    }

                    // Delete non-global-admin users associated with this shop
                    if (!empty($globalAdminIds)) {
                        DB::table('users')->where('barber_shop_id', $shopId)->whereNotIn('id', $globalAdminIds)->delete();
                    } else {
                        DB::table('users')->where('barber_shop_id', $shopId)->delete();
                    }

                    // Unlink remaining users
                    DB::table('users')->where('barber_shop_id', $shopId)->update(['barber_shop_id' => null]);
                }

                // Delete Logo and Banner images if stored on disk
                if ($model->logo) { app(\App\Services\ImageUploadService::class)->delete($model->logo); }
                if ($model->banner) { app(\App\Services\ImageUploadService::class)->delete($model->banner); }

                AuditTrail::log($model, 'delete', 'BarberShops');
                Log::info("🗑️ [BarberShop Purge] Successfully cascade-deleted all records and relationships for Shop #{$shopId} ('{$model->name}')");

                DB::table('barber_shops')->where('id', $shopId)->delete();

                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } catch (\Throwable $e) {}

                return true;

            } catch (\Throwable $e) {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } catch (\Throwable $e2) {}

                Log::error("❌ [BarberShop Delete Error] Shop #{$shopId}: " . $e->getMessage());
                throw $e;
            }
        });
    }
}
