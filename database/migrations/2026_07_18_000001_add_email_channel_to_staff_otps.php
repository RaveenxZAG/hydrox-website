<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_otps', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_otps', 'normalized_email')) {
                $table->string('normalized_email')->nullable()->index()->after('normalized_mobile');
            }

            if (! Schema::hasColumn('staff_otps', 'delivery_channel')) {
                $table->string('delivery_channel', 20)->default('sms')->index()->after('normalized_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_otps', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_otps', 'delivery_channel')) {
                $table->dropColumn('delivery_channel');
            }

            if (Schema::hasColumn('staff_otps', 'normalized_email')) {
                $table->dropColumn('normalized_email');
            }
        });
    }
};
