<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('customer_number')->unique();
            $table->string('customer_name');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('suburb')->nullable();
            $table->string('state', 50)->nullable();
            $table->string('postcode', 16)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('cleaning_service');
            $table->date('booking_date');
            $table->time('start_time')->nullable();
            $table->time('finish_time')->nullable();
            $table->string('technician')->nullable();
            $table->string('priority')->default('Normal');
            $table->string('status')->default('Pending')->index();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('completion_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('job_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('completion_date');
            $table->string('technician');
            $table->string('weather')->nullable();
            $table->string('overall_condition');
            $table->boolean('customer_present')->default(false);
            $table->json('checklist')->nullable();
            $table->text('work_summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('technician_name')->nullable();
            $table->string('technician_signature_path')->nullable();
            $table->timestamp('technician_signed_at')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_signature_path')->nullable();
            $table->timestamp('customer_signed_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status')->default('Draft')->index();
            $table->timestamps();
        });

        Schema::create('cleaning_areas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('completion_report_id')->constrained()->cascadeOnDelete();
            $table->string('area_name');
            $table->text('description')->nullable();
            $table->text('completion_notes')->nullable();
            $table->text('photo_notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('area_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cleaning_area_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_useds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('completion_report_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('quantity')->nullable();
            $table->string('equipment_used')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_founds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('completion_report_id')->constrained()->cascadeOnDelete();
            $table->string('issue_type');
            $table->text('description')->nullable();
            $table->string('severity')->default('Low');
            $table->string('photo_path')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('issue_found_id')->constrained('issue_founds')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_photos');
        Schema::dropIfExists('issue_founds');
        Schema::dropIfExists('product_useds');
        Schema::dropIfExists('area_photos');
        Schema::dropIfExists('cleaning_areas');
        Schema::dropIfExists('completion_reports');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('customers');
    }
};
