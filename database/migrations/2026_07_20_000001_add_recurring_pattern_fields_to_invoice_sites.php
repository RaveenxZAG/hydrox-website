<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_sites', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoice_sites', 'site_code')) {
                $table->string('site_code', 20)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('invoice_sites', 'recurring_pattern')) {
                $table->string('recurring_pattern', 30)->default('weekly')->after('active');
            }
            if (! Schema::hasColumn('invoice_sites', 'validation_mode')) {
                $table->string('validation_mode', 30)->default('auto')->after('recurring_pattern');
            }
            if (! Schema::hasColumn('invoice_sites', 'fortnightly_anchor_date')) {
                $table->date('fortnightly_anchor_date')->nullable()->after('validation_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_sites', function (Blueprint $table): void {
            foreach ([
                'fortnightly_anchor_date',
                'validation_mode',
                'recurring_pattern',
                'site_code',
            ] as $column) {
                if (Schema::hasColumn('invoice_sites', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
