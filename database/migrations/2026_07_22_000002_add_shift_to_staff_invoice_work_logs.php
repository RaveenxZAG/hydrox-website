<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_invoice_work_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_invoice_work_logs', 'invoice_site_shift_id')) {
                $table->unsignedBigInteger('invoice_site_shift_id')
                    ->nullable()
                    ->after('invoice_site_id')
                    ->index();
            }
            if (! Schema::hasColumn('staff_invoice_work_logs', 'shift_label')) {
                $table->string('shift_label', 80)->nullable()->after('site_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_invoice_work_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_invoice_work_logs', 'invoice_site_shift_id')) {
                $table->dropColumn('invoice_site_shift_id');
            }
            if (Schema::hasColumn('staff_invoice_work_logs', 'shift_label')) {
                $table->dropColumn('shift_label');
            }
        });
    }
};
