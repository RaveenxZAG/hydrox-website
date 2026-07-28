<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jobs') || ! Schema::hasColumn('jobs', 'customer_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql' => DB::statement('ALTER TABLE jobs MODIFY customer_id BIGINT UNSIGNED NULL'),
            'pgsql' => DB::statement('ALTER TABLE jobs ALTER COLUMN customer_id DROP NOT NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('jobs') || ! Schema::hasColumn('jobs', 'customer_id')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql' => DB::statement('ALTER TABLE jobs MODIFY customer_id BIGINT UNSIGNED NOT NULL'),
            'pgsql' => DB::statement('ALTER TABLE jobs ALTER COLUMN customer_id SET NOT NULL'),
            default => null,
        };
    }
};
