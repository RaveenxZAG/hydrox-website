<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice_sites')) {
            Schema::create('invoice_sites', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('active')->default(true)->index();
                $table->decimal('monday_hours', 8, 2)->default(0);
                $table->decimal('tuesday_hours', 8, 2)->default(0);
                $table->decimal('wednesday_hours', 8, 2)->default(0);
                $table->decimal('thursday_hours', 8, 2)->default(0);
                $table->decimal('friday_hours', 8, 2)->default(0);
                $table->decimal('saturday_hours', 8, 2)->default(0);
                $table->decimal('sunday_hours', 8, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('staff_invoice_work_logs')) {
            Schema::create('staff_invoice_work_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_invoice_submission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
                $table->foreignId('invoice_site_id')->nullable()->constrained()->nullOnDelete();
                $table->date('work_date')->nullable()->index();
                $table->string('site_name');
                $table->string('work_type')->default('regular')->index();
                $table->decimal('hours', 8, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->unsignedInteger('row_number')->nullable();
                $table->string('status')->default('ok')->index();
                $table->text('flag_reason')->nullable();
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('staff_invoice_archives')) {
            Schema::create('staff_invoice_archives', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_invoice_submission_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('version')->default(1);
                $table->string('status')->nullable();
                $table->decimal('total_amount', 12, 2)->nullable();
                $table->string('original_filename')->nullable();
                $table->string('storage_path')->nullable();
                $table->json('work_logs')->nullable();
                $table->text('reason')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('staff_invoice_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_invoice_submissions', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('invoice_period');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'approved_total')) {
                $table->decimal('approved_total', 12, 2)->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'correction_reason')) {
                $table->text('correction_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'correction_instructions')) {
                $table->text('correction_instructions')->nullable()->after('correction_reason');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'correction_due_at')) {
                $table->timestamp('correction_due_at')->nullable()->after('correction_instructions');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('submitted_ip');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'ready_for_payment_at')) {
                $table->timestamp('ready_for_payment_at')->nullable()->after('reviewed_at');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('ready_for_payment_at');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('staff_invoice_submissions', 'remittance_path')) {
                $table->string('remittance_path')->nullable()->after('payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invoice_archives');
        Schema::dropIfExists('staff_invoice_work_logs');
        Schema::dropIfExists('invoice_sites');

        Schema::table('staff_invoice_submissions', function (Blueprint $table): void {
            foreach ([
                'version',
                'approved_total',
                'correction_reason',
                'correction_instructions',
                'correction_due_at',
                'reviewed_at',
                'ready_for_payment_at',
                'paid_at',
                'payment_reference',
                'remittance_path',
            ] as $column) {
                if (Schema::hasColumn('staff_invoice_submissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
