<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Upgrade existing plain text rows into valid JSON format first
        $records = DB::table('message_templates')->get();
        foreach ($records as $record) {
            $content = $record->content;
            if ($content && !str_starts_with(trim($content), '{')) {
                $jsonContent = json_encode([
                    'sq' => $content,
                    'en' => $content,
                ], JSON_UNESCAPED_UNICODE);

                DB::table('message_templates')
                    ->where('id', $record->id)
                    ->update(['content' => $jsonContent]);
            }
        }

        // 2. Keep column as TEXT (MySQL TEXT handles both plain text and JSON strings with Eloquent array cast)
        Schema::table('message_templates', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }
};
