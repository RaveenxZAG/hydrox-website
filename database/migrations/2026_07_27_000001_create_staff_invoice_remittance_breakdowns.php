<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_invoice_remittance_breakdowns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_invoice_submission_id')
                ->unique('remittance_breakdown_invoice_unique');
            $table->foreignId('staff_member_id');
            $table->foreign(
                'staff_invoice_submission_id',
                'remittance_breakdown_invoice_fk'
            )->references('id')->on('staff_invoice_submissions')->cascadeOnDelete();
            $table->foreign(
                'staff_member_id',
                'remittance_breakdown_staff_fk'
            )->references('id')->on('staff_members')->cascadeOnDelete();
            $table->decimal('approved_total', 12, 2);
            $table->decimal('fuel_percentage', 7, 4);
            $table->decimal('fuel_amount', 12, 2);
            $table->decimal('tools_materials_percentage', 7, 4);
            $table->decimal('tools_materials_amount', 12, 2);
            $table->decimal('tools_split_percentage', 7, 4)->nullable();
            $table->decimal('tools_amount', 12, 2)->nullable();
            $table->decimal('materials_split_percentage', 7, 4)->nullable();
            $table->decimal('materials_amount', 12, 2)->nullable();
            $table->decimal('labour_percentage', 7, 4);
            $table->decimal('labour_amount', 12, 2);
            $table->timestamp('generated_at');
            $table->string('generation_status')->default('generated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invoice_remittance_breakdowns');
    }
};
