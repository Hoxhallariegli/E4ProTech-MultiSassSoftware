<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->boolean('is_sms_gateway')->default(false)->after('platform');
            $table->string('device_name')->nullable()->after('is_sms_gateway');
        });
    }

    public function down(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropColumn(['is_sms_gateway', 'device_name']);
        });
    }
};
