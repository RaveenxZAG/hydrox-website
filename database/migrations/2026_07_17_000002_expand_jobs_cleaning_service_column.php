<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jobs') || ! Schema::hasColumn('jobs', 'cleaning_service')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql' => DB::statement('ALTER TABLE jobs MODIFY cleaning_service TEXT NOT NULL'),
            'pgsql' => DB::statement('ALTER TABLE jobs ALTER COLUMN cleaning_service TYPE TEXT'),
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('jobs') || ! Schema::hasColumn('jobs', 'cleaning_service')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql' => DB::statement('ALTER TABLE jobs MODIFY cleaning_service VARCHAR(255) NOT NULL'),
            'pgsql' => DB::statement('ALTER TABLE jobs ALTER COLUMN cleaning_service TYPE VARCHAR(255)'),
            default => null,
        };
    }
};
