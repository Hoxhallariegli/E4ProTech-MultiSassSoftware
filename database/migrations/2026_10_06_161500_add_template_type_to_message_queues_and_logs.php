<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('message_queues', 'template_type')) {
            Schema::table('message_queues', function (Blueprint $table) {
                $table->string('template_type')->nullable()->after('channel');
            });
        }

        if (!Schema::hasColumn('message_logs', 'template_type')) {
            Schema::table('message_logs', function (Blueprint $table) {
                $table->string('template_type')->nullable()->after('channel');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('message_queues', 'template_type')) {
            Schema::table('message_queues', function (Blueprint $table) {
                $table->dropColumn('template_type');
            });
        }

        if (Schema::hasColumn('message_logs', 'template_type')) {
            Schema::table('message_logs', function (Blueprint $table) {
                $table->dropColumn('template_type');
            });
        }
    }
};
