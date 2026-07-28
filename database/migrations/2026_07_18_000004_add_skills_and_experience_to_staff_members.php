<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_members', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_members', 'skills')) {
                $table->json('skills')->nullable()->after('availability');
            }

            if (! Schema::hasColumn('staff_members', 'experience')) {
                $table->text('experience')->nullable()->after('skills');
            }
        });

        try {
            if (Schema::hasColumn('subcontractor_onboardings', 'skills')) {
                DB::table('staff_members')
                    ->join('subcontractor_onboardings', 'staff_members.subcontractor_onboarding_id', '=', 'subcontractor_onboardings.id')
                    ->whereNull('staff_members.skills')
                    ->whereNotNull('subcontractor_onboardings.skills')
                    ->update(['staff_members.skills' => DB::raw('subcontractor_onboardings.skills')]);
            }

            if (Schema::hasColumn('subcontractor_onboardings', 'experience')) {
                DB::table('staff_members')
                    ->join('subcontractor_onboardings', 'staff_members.subcontractor_onboarding_id', '=', 'subcontractor_onboardings.id')
                    ->whereNull('staff_members.experience')
                    ->whereNotNull('subcontractor_onboardings.experience')
                    ->update(['staff_members.experience' => DB::raw('subcontractor_onboardings.experience')]);
            }
        } catch (Throwable) {
            // Backfill is best-effort for local databases that predate the onboarding skills column.
        }
    }

    public function down(): void
    {
        Schema::table('staff_members', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_members', 'experience')) {
                $table->dropColumn('experience');
            }

            if (Schema::hasColumn('staff_members', 'skills')) {
                $table->dropColumn('skills');
            }
        });
    }
};
