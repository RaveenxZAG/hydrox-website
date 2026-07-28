<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'servicem8_uuid')) {
                $table->string('servicem8_uuid')->nullable()->index()->after('notes');
            }

            if (! Schema::hasColumn('customers', 'servicem8_synced_at')) {
                $table->timestamp('servicem8_synced_at')->nullable()->after('servicem8_uuid');
            }

            if (! Schema::hasColumn('customers', 'servicem8_edit_date')) {
                $table->timestamp('servicem8_edit_date')->nullable()->after('servicem8_synced_at');
            }

            if (! Schema::hasColumn('customers', 'servicem8_dirty')) {
                $table->boolean('servicem8_dirty')->default(false)->after('servicem8_edit_date');
            }

            if (! Schema::hasColumn('customers', 'servicem8_sync_status')) {
                $table->string('servicem8_sync_status')->nullable()->index()->after('servicem8_dirty');
            }

            if (! Schema::hasColumn('customers', 'servicem8_sync_message')) {
                $table->text('servicem8_sync_message')->nullable()->after('servicem8_sync_status');
            }
        });

        Schema::table('jobs', function (Blueprint $table): void {
            if (! Schema::hasColumn('jobs', 'servicem8_uuid')) {
                $table->string('servicem8_uuid')->nullable()->index()->after('internal_notes');
            }

            if (! Schema::hasColumn('jobs', 'servicem8_synced_at')) {
                $table->timestamp('servicem8_synced_at')->nullable()->after('servicem8_uuid');
            }

            if (! Schema::hasColumn('jobs', 'servicem8_edit_date')) {
                $table->timestamp('servicem8_edit_date')->nullable()->after('servicem8_synced_at');
            }

            if (! Schema::hasColumn('jobs', 'servicem8_dirty')) {
                $table->boolean('servicem8_dirty')->default(false)->after('servicem8_edit_date');
            }

            if (! Schema::hasColumn('jobs', 'servicem8_sync_status')) {
                $table->string('servicem8_sync_status')->nullable()->index()->after('servicem8_dirty');
            }

            if (! Schema::hasColumn('jobs', 'servicem8_sync_message')) {
                $table->text('servicem8_sync_message')->nullable()->after('servicem8_sync_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            foreach ([
                'servicem8_sync_message',
                'servicem8_sync_status',
                'servicem8_dirty',
                'servicem8_edit_date',
                'servicem8_synced_at',
                'servicem8_uuid',
            ] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('jobs', function (Blueprint $table): void {
            foreach ([
                'servicem8_sync_message',
                'servicem8_sync_status',
                'servicem8_dirty',
                'servicem8_edit_date',
                'servicem8_synced_at',
                'servicem8_uuid',
            ] as $column) {
                if (Schema::hasColumn('jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
