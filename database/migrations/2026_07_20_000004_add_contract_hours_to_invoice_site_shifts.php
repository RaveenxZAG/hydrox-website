<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_site_shifts')) {
            return;
        }

        Schema::table('invoice_site_shifts', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoice_site_shifts', 'contract_hours')) {
                $table->decimal('contract_hours', 8, 2)->default(0)->after('label');
            }
        });

        DB::table('invoice_site_shifts')
            ->where(function ($query): void {
                $query->whereNull('contract_hours')->orWhere('contract_hours', 0);
            })
            ->update(['contract_hours' => DB::raw('hours')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoice_site_shifts') || ! Schema::hasColumn('invoice_site_shifts', 'contract_hours')) {
            return;
        }

        Schema::table('invoice_site_shifts', function (Blueprint $table): void {
            $table->dropColumn('contract_hours');
        });
    }
};
