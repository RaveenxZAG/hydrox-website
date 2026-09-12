<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('HYDROX_ADMIN_PASSWORD');

        if (blank($password)) {
            $this->command?->warn('HYDROX_ADMIN_PASSWORD is not set; the portal administrator was not seeded.');

            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@hydrox.au'],
            ['name' => 'Hydrox Administrator', 'password' => $password]
        );

        if (! \App\Models\SystemSetting::query()->where('key', 'business_information')->exists()) {
            \App\Models\SystemSetting::setValue('business_information', json_encode(\App\Models\SystemSetting::businessInformation(), JSON_UNESCAPED_SLASHES));
            $this->command?->info('Default business information initialized.');
        }
    }
}
