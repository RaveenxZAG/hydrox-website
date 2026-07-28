<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_members', function (Blueprint $table): void {
            foreach ([
                'legal_business_name',
                'trading_name',
                'abn',
                'business_structure',
                'contact_person',
                'business_address',
                'availability',
                'bank_details',
                'superannuation',
                'public_liability_insurance',
                'public_liability_insurance_name',
                'workers_compensation_insurance',
                'workers_compensation_insurance_name',
                'police_clearance',
                'police_clearance_name',
                'driver_licence',
                'driver_licence_name',
                'working_rights',
                'working_rights_name',
            ] as $column) {
                if (! Schema::hasColumn('staff_members', $column)) {
                    $table->text($column)->nullable();
                }
            }

            if (! Schema::hasColumn('staff_members', 'gst_registered')) {
                $table->boolean('gst_registered')->default(false);
            }

            if (! Schema::hasColumn('staff_members', 'insurance_expiry')) {
                $table->date('insurance_expiry')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_members', function (Blueprint $table): void {
            foreach ([
                'legal_business_name',
                'trading_name',
                'abn',
                'gst_registered',
                'business_structure',
                'contact_person',
                'business_address',
                'availability',
                'bank_details',
                'superannuation',
                'insurance_expiry',
                'public_liability_insurance',
                'public_liability_insurance_name',
                'workers_compensation_insurance',
                'workers_compensation_insurance_name',
                'police_clearance',
                'police_clearance_name',
                'driver_licence',
                'driver_licence_name',
                'working_rights',
                'working_rights_name',
            ] as $column) {
                if (Schema::hasColumn('staff_members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
