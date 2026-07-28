<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_sites', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoice_sites', 'weekly_contract_hours')) {
                $table->decimal('weekly_contract_hours', 8, 2)->default(0)->after('fortnightly_anchor_date');
            }
        });

        if (Schema::hasTable('invoice_site_shifts')) {
            $siteHours = DB::table('invoice_site_shifts')
                ->select('invoice_site_id', DB::raw('SUM(CASE WHEN contract_hours > 0 THEN contract_hours ELSE hours END) as weekly_total'))
                ->groupBy('invoice_site_id')
                ->pluck('weekly_total', 'invoice_site_id');

            foreach ($siteHours as $siteId => $weeklyTotal) {
                DB::table('invoice_sites')
                    ->where('id', $siteId)
                    ->where(function ($query): void {
                        $query->whereNull('weekly_contract_hours')->orWhere('weekly_contract_hours', 0);
                    })
                    ->update(['weekly_contract_hours' => round((float) $weeklyTotal, 2)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('invoice_sites', function (Blueprint $table): void {
            if (Schema::hasColumn('invoice_sites', 'weekly_contract_hours')) {
                $table->dropColumn('weekly_contract_hours');
            }
        });
    }
};
