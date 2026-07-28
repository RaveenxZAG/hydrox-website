<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_invoice_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_invoice_submissions', 'remittance_servicem8_attachment_uuid')) {
                $table->uuid('remittance_servicem8_attachment_uuid')->nullable()->after('remittance_path');
            }
        });

        if (! Schema::hasTable('staff_invoice_remittance_deliveries')) {
            Schema::create('staff_invoice_remittance_deliveries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_invoice_submission_id');
                $table->foreign('staff_invoice_submission_id', 'remittance_delivery_invoice_fk')
                    ->references('id')
                    ->on('staff_invoice_submissions')
                    ->cascadeOnDelete();
                $table->string('channel', 20)->index();
                $table->string('recipient')->nullable();
                $table->string('status', 20)->index();
                $table->string('attempt_type', 20)->default('automatic');
                $table->text('error_message')->nullable();
                $table->timestamp('attempted_at');
                $table->timestamps();
            });
        } else {
            // Repairs a partially created table if an earlier deployment failed on MySQL's identifier limit.
            Schema::table('staff_invoice_remittance_deliveries', function (Blueprint $table): void {
                $table->foreign('staff_invoice_submission_id', 'remittance_delivery_invoice_fk')
                    ->references('id')
                    ->on('staff_invoice_submissions')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invoice_remittance_deliveries');

        Schema::table('staff_invoice_submissions', function (Blueprint $table): void {
            if (Schema::hasColumn('staff_invoice_submissions', 'remittance_servicem8_attachment_uuid')) {
                $table->dropColumn('remittance_servicem8_attachment_uuid');
            }
        });
    }
};
