<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateMysqlToSqliteCommand extends Command
{
    protected $signature = 'hydrox:migrate-to-sqlite
        {--source=mysql_source : The database connection to read from}
        {--target=sqlite : The database connection to write to}
        {--chunk=500 : Number of rows per chunk}
        {--dry-run : Count and report without copying data}
        {--force : Run without confirmation}
        {--skip-tables= : Comma-separated list of table names to skip}';

    protected $description = 'Migrate data from MySQL to SQLite database safely with integrity checks';

    /**
     * Canonical order of tables for Hydrox Website to preserve logical dependencies.
     */
    protected array $orderedTables = [
        'users',
        'sessions',
        'cache',
        'cache_locks',
        'failed_jobs',
        'queue_jobs',
        'system_settings',
        'system_notifications',
        'customers',
        'jobs',
        'cleaning_areas',
        'area_photos',
        'issue_founds',
        'issue_photos',
        'product_useds',
        'completion_reports',
        'client_contacts',
        'staff_members',
        'staff_otps',
        'staff_identity_locks',
        'staff_profile_change_requests',
        'staff_broadcasts',
        'staff_broadcast_recipients',
        'invoice_sites',
        'invoice_site_shifts',
        'site_shift_assignments',
        'site_assignment_deliveries',
        'staff_invoice_work_logs',
        'staff_invoice_remittance_deliveries',
        'staff_invoice_archives',
        'monthly_invoice_ai_summaries',
        'monthly_xero_reports',
        'invoice_ai_reviews',
        'subcontractor_onboardings',
        'subcontractor_documents',
        'subcontractor_document_versions',
        'bookings',
        'booking_photos',
        'company_profile_email_deliveries',
    ];

    public function handle(): int
    {
        $source = (string) $this->option('source');
        $target = (string) $this->option('target');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $skipOption = (string) $this->option('skip-tables');
        $skippedTables = array_filter(array_map('trim', explode(',', $skipOption)));

        $this->info("Source connection: {$source}");
        $this->info("Target connection: {$target}");
        if ($dryRun) {
            $this->warn('DRY RUN: No records will be modified or copied.');
        }

        // 1. Verify connections
        try {
            $sourceConn = DB::connection($source);
            $sourceConn->getPdo();
            $this->info("Successfully connected to source database ({$source}).");
        } catch (\Throwable $e) {
            $this->error("Failed to connect to source database ({$source}): " . $e->getMessage());
            return 1;
        }

        try {
            $targetConn = DB::connection($target);
            $targetConn->getPdo();
            $this->info("Successfully connected to target database ({$target}).");
        } catch (\Throwable $e) {
            $this->error("Failed to connect to target database ({$target}): " . $e->getMessage());
            return 1;
        }

        $isTargetSqlite = $targetConn->getDriverName() === 'sqlite';

        // 2. Discover tables from target schema
        $rawTargetTables = Schema::connection($target)->getTableListing();
        $targetTables = [];
        foreach ($rawTargetTables as $t) {
            // Strip sqlite schema prefix if present e.g. "main.users" -> "users"
            $cleanName = str_contains($t, '.') ? substr($t, strpos($t, '.') + 1) : $t;
            if ($cleanName !== 'migrations') {
                $targetTables[] = $cleanName;
            }
        }

        // Sort tables using orderedTables prioritizing known dependencies, then append any other tables
        $tablesToMigrate = [];
        foreach ($this->orderedTables as $tbl) {
            if (in_array($tbl, $targetTables, true)) {
                $tablesToMigrate[] = $tbl;
            }
        }
        foreach ($targetTables as $tbl) {
            if (!in_array($tbl, $tablesToMigrate, true)) {
                $tablesToMigrate[] = $tbl;
            }
        }

        // Remove skipped tables
        $tablesToMigrate = array_values(array_filter($tablesToMigrate, fn($t) => !in_array($t, $skippedTables, true)));

        $this->info(sprintf('Discovered %d tables for migration.', count($tablesToMigrate)));

        if (!$dryRun && !$force) {
            if (!$this->confirm('This will copy data from source to target tables. Existing target data will be replaced. Proceed?', false)) {
                $this->warn('Migration aborted by user.');
                return 0;
            }
        }

        // 3. Prepare target SQLite settings
        if ($isTargetSqlite && !$dryRun) {
            $targetConn->statement('PRAGMA foreign_keys = OFF');
            $targetConn->statement('PRAGMA synchronous = OFF');
        }

        $summary = [];
        $hasErrors = false;

        // 4. Migrate tables
        foreach ($tablesToMigrate as $table) {
            // Check table exists in source
            if (!Schema::connection($source)->hasTable($table)) {
                $this->warn("Table [{$table}] does not exist in source. Skipping.");
                $summary[] = [
                    'table' => $table,
                    'source_rows' => 'N/A',
                    'target_rows' => $targetConn->table($table)->count(),
                    'status' => 'SKIPPED_NOT_IN_SOURCE',
                ];
                continue;
            }

            $sourceCount = $sourceConn->table($table)->count();

            if ($dryRun) {
                $targetCount = $targetConn->table($table)->count();
                $summary[] = [
                    'table' => $table,
                    'source_rows' => $sourceCount,
                    'target_rows' => $targetCount,
                    'status' => 'DRY_RUN',
                ];
                continue;
            }

            $this->line("Migrating table [{$table}] ({$sourceCount} rows)...");

            try {
                // Clear target table before populating
                $targetConn->table($table)->delete();

                if ($sourceCount > 0) {
                    for ($offset = 0; $offset < $sourceCount; $offset += $chunkSize) {
                        $rows = $sourceConn->table($table)
                            ->offset($offset)
                            ->limit($chunkSize)
                            ->get();

                        $data = [];
                        foreach ($rows as $row) {
                            $data[] = (array) $row;
                        }

                        if (!empty($data)) {
                            $targetConn->table($table)->insert($data);
                        }
                    }
                }

                $targetCount = $targetConn->table($table)->count();

                if ($sourceCount === $targetCount) {
                    $status = 'OK';
                } else {
                    $status = "MISMATCH (src: {$sourceCount}, tgt: {$targetCount})";
                    $hasErrors = true;
                }

                $summary[] = [
                    'table' => $table,
                    'source_rows' => $sourceCount,
                    'target_rows' => $targetCount,
                    'status' => $status,
                ];
            } catch (\Throwable $e) {
                $this->error("Error migrating table [{$table}]: " . $e->getMessage());
                $hasErrors = true;
                $summary[] = [
                    'table' => $table,
                    'source_rows' => $sourceCount,
                    'target_rows' => 'ERROR',
                    'status' => 'ERROR: ' . $e->getMessage(),
                ];
            }
        }

        // 5. Restore target SQLite settings and verify foreign keys
        if ($isTargetSqlite && !$dryRun) {
            $targetConn->statement('PRAGMA foreign_keys = ON');
            $targetConn->statement('PRAGMA synchronous = NORMAL');
            $targetConn->statement('PRAGMA journal_mode = WAL');

            $this->info('Running PRAGMA foreign_key_check on target SQLite database...');
            $fkViolations = $targetConn->select('PRAGMA foreign_key_check');

            if (!empty($fkViolations)) {
                $hasErrors = true;
                $this->error('Foreign key check failed with violations:');
                $this->table(['table', 'rowid', 'parent_table', 'fkid'], array_map(fn($v) => (array) $v, $fkViolations));
            } else {
                $this->info('PRAGMA foreign_key_check passed with 0 violations.');
            }

            $this->info('Running SQLite VACUUM and ANALYZE...');
            $targetConn->statement('VACUUM');
            $targetConn->statement('ANALYZE');
        }

        // 6. Display summary table
        $this->newLine();
        $this->table(['Table', 'Source Count', 'Target Count', 'Status'], $summary);

        if ($hasErrors) {
            $this->error('Migration finished with errors or row mismatches.');
            return 1;
        }

        $this->info('Migration completed successfully.');
        return 0;
    }
}
