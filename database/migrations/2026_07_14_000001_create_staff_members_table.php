<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subcontractor_onboarding_id')->nullable()->constrained()->nullOnDelete();
            $table->string('servicem8_staff_uuid')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('mobile')->nullable();
            $table->string('job_title')->nullable();
            $table->string('security_role')->nullable();
            $table->string('schedule_colour')->nullable();
            $table->decimal('labour_rate', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('show_on_schedule')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('servicem8_synced_at')->nullable();
            $table->text('servicem8_sync_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_members');
    }
};
