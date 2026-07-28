<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $setting = DB::table('system_settings')
            ->where('key', 'business_information')
            ->first();

        $business = $setting ? json_decode((string) $setting->value, true) : [];
        $business = is_array($business) ? $business : [];

        $business = array_merge($business, [
            'phone' => '0418 222 477',
            'mobile_primary' => '0418 222 477',
            'mobile_secondary' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'address_line_3' => '',
            'city' => '',
            'state' => '',
            'postcode' => '',
            'country' => '',
        ]);

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'business_information'],
            [
                'value' => json_encode($business, JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
                'created_at' => $setting?->created_at ?? now(),
            ]
        );
    }

    public function down(): void
    {
        // The previous company's address and phone details must not be restored.
    }
};
