<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_invoice_ai_summaries', function (Blueprint $table): void {
            $table->id();
            $table->date('invoice_month')->unique();
            $table->foreignId('monthly_xero_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('gemini');
            $table->string('status')->default('pending')->index();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->json('site_hour_summary')->nullable();
            $table->json('invoice_summaries')->nullable();
            $table->text('summary')->nullable();
            $table->text('mismatch_notes')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_invoice_ai_summaries');
    }
};
