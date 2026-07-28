<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_site_shifts')) {
            Schema::create('invoice_site_shifts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('invoice_site_id')->constrained()->cascadeOnDelete();
                $table->string('weekday', 20)->index();
                $table->string('label', 80)->default('Shift A');
                $table->decimal('hours', 8, 2)->default(0);
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
            });
        }

        Schema::table('staff_invoice_work_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_invoice_work_logs', 'service_m8_job_code')) {
                $table->string('service_m8_job_code')->nullable()->after('site_name')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_invoice_work_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_invoice_work_logs', 'service_m8_job_code')) {
                $table->dropColumn('service_m8_job_code');
            }
        });

        Schema::dropIfExists('invoice_site_shifts');
    }
};
