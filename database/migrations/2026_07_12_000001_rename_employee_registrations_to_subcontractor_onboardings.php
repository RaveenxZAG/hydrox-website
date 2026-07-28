<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_registrations') && ! Schema::hasTable('subcontractor_onboardings')) {
            Schema::rename('employee_registrations', 'subcontractor_onboardings');
        }

        Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
            if (! Schema::hasColumn('subcontractor_onboardings', 'status')) {
                $table->string('status')->default('Pending Review')->index()->after('id');
            }
            if (! Schema::hasColumn('subcontractor_onboardings', 'legal_business_name')) {
                $table->string('legal_business_name')->nullable()->after('status');
                $table->string('trading_name')->nullable()->after('legal_business_name');
                $table->string('abn', 32)->nullable()->after('trading_name');
                $table->boolean('gst_registered')->default(false)->after('abn');
                $table->string('business_structure')->nullable()->after('gst_registered');
                $table->string('contact_person')->nullable()->after('business_structure');
                $table->string('mobile')->nullable()->after('phone');
                $table->string('business_address')->nullable()->after('address');
                $table->string('public_liability_insurance')->nullable();
                $table->string('workers_compensation_insurance')->nullable();
                $table->date('insurance_expiry')->nullable();
                $table->string('police_clearance')->nullable();
                $table->string('driver_licence')->nullable();
                $table->string('working_rights')->nullable();
                $table->json('uploaded_certificates')->nullable();
                $table->json('service_types')->nullable();
                $table->json('preferred_work_areas')->nullable();
                $table->json('available_days')->nullable();
                $table->string('available_hours')->nullable();
                $table->unsignedInteger('crew_members')->nullable();
                $table->string('supervisor')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_frequency')->nullable();
                $table->text('bank_details')->nullable();
                $table->text('superannuation')->nullable();
                $table->string('servicem8_staff_uuid')->nullable()->index();
                $table->string('servicem8_sync_status')->nullable();
                $table->text('servicem8_sync_message')->nullable();
                $table->json('sync_logs')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('rejected_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('portal_user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('subcontractor_onboardings', 'full_name')) {
            Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
                if (! Schema::hasColumn('subcontractor_onboardings', 'first_name')) {
                    $table->string('first_name')->nullable()->after('full_name');
                    $table->string('last_name')->nullable()->after('first_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subcontractor_onboardings') && ! Schema::hasTable('employee_registrations')) {
            Schema::rename('subcontractor_onboardings', 'employee_registrations');
        }
    }
};
