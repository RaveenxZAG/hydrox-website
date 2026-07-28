<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subcontractor_onboardings') && Schema::hasColumn('subcontractor_onboardings', 'available_hours')) {
            Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
                $table->text('available_hours')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subcontractor_onboardings') && Schema::hasColumn('subcontractor_onboardings', 'available_hours')) {
            Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
                $table->string('available_hours')->nullable()->change();
            });
        }
    }
};
