<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_invoice_ai_summaries', function (Blueprint $table): void {
            if (! Schema::hasColumn('monthly_invoice_ai_summaries', 'error_message')) {
                $table->text('error_message')->nullable()->after('mismatch_notes');
            }
            if (! Schema::hasColumn('monthly_invoice_ai_summaries', 'uploaded_files')) {
                $table->json('uploaded_files')->nullable()->after('response');
            }
            if (! Schema::hasColumn('monthly_invoice_ai_summaries', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('uploaded_files');
            }
            if (! Schema::hasColumn('monthly_invoice_ai_summaries', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_invoice_ai_summaries', function (Blueprint $table): void {
            foreach (['completed_at', 'started_at', 'uploaded_files', 'error_message'] as $column) {
                if (Schema::hasColumn('monthly_invoice_ai_summaries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
