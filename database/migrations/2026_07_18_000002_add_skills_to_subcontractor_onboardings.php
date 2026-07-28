<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
            if (! Schema::hasColumn('subcontractor_onboardings', 'skills')) {
                $table->json('skills')->nullable()->after('experience');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
            if (Schema::hasColumn('subcontractor_onboardings', 'skills')) {
                $table->dropColumn('skills');
            }
        });
    }
};
