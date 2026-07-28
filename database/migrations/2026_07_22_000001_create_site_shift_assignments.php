<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_shift_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_site_shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('active')->default(true)->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();
            $table->unique(['invoice_site_shift_id', 'staff_member_id'], 'site_shift_staff_unique');
        });

        Schema::create('site_assignment_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_shift_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 30);
            $table->string('channel', 20);
            $table->string('recipient')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('message')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_assignment_deliveries');
        Schema::dropIfExists('site_shift_assignments');
    }
};
