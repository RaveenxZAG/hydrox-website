<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table): void {
            $table->timestamp('telegram_sent_at')->nullable()->after('emailed_at');
            $table->text('telegram_error')->nullable()->after('telegram_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table): void {
            $table->dropColumn(['telegram_sent_at', 'telegram_error']);
        });
    }
};
