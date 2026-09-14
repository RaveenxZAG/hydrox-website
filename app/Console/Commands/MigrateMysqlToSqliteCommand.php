<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MigrateMysqlToSqliteCommand extends Command
{
    protected $signature = 'hydrox:migrate-to-sqlite
        {--source=mysql_source : The database connection to read from}
        {--target-path= : Target SQLite file path (defaults to sqlite database config)}
        {--staging-path= : Custom staging SQLite file path (optional)}
        {--chunk=500 : Number of rows per chunk}
        {--dry-run : Count and report without copying data}
        {--force : Run without interactive confirmation}
        {--skip-tables= : Comma-separated list of table names to skip}';

    protected $description = 'Safely build a staging SQLite database from MySQL, verify schema/FKs, and atomically promote it';

    /**
     * Canonical order of tables for Hydrox Website to preserve logical foreign key dependencies.
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
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $skipOption = (string) $this->option('skip-tables');
        $skippedTables = array_filter(array_map('trim', explode(',', $skipOption)));

        // 1. Validate explicit source database credentials (fail closed, no silent fallback to active DB)
        $sourceConfig = config("database.connections.{$source}", []);
        if (empty($sourceConfig)) {
            $this->error("Source connection [{$source}] is not defined in config/database.php.");
            return 1;
        }

        $sourceDb = $sourceConfig['database'] ?? null;
        $sourceUser = $sourceConfig['username'] ?? null;
        $sourcePass = $sourceConfig['password'] ?? null;

        if (empty($sourceDb) || empty($sourceUser)) {
            $this->error("Missing required source credentials (SOURCE_DB_DATABASE and SOURCE_DB_USERNAME). Silent fallback to active DB is disabled.");
            return 1;
        }

        // 2. Resolve destination path
        $targetPath = $this->option('target-path') ?: config('database.connections.sqlite.database');
        if (empty($targetPath) || $targetPath === ':memory:') {
            $this->error("Invalid SQLite target path: [{$targetPath}]. Cannot migrate to memory or empty path.");
            return 1;
        }

        $targetDir = dirname($targetPath);
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0750, true, true);
        }

        // 3. Concurrency Lock: Prevent multiple imports from running concurrently
        $lockFile = $targetDir . '/.sqlite_migration.lock';
        if (File::exists($lockFile)) {
            $lockAge = time() - File::lastModified($lockFile);
            if ($lockAge < 1800) { // 30 minutes
                $this->error("Migration is locked: another import is currently in progress (lock file: {$lockFile}, age: {$lockAge}s).");
                return 1;
            }
            $this->warn("Removing stale lock file (age: {$lockAge}s).");
            File::delete($lockFile);
        }

        File::put($lockFile, json_encode([
            'started_at' => now()->toIso8601String(),
            'pid' => getmypid(),
            'source' => $source,
            'target' => $targetPath,
        ]));

        try {
            // 4. Test source connection
            try {
                $sourceConn = DB::connection($source);
                $sourceConn->getPdo();
                $this->info("Successfully connected to MySQL source [{$source}].");
            } catch (\Throwable $e) {
                $this->error("Failed to connect to source database ({$source}): " . $e->getMessage());
                return 1;
            }

            // 5. Handle Dry Run
            if ($dryRun) {
                $this->warn("DRY RUN: Inspecting source database records without creating or modifying SQLite files.");
                $tables = $this->orderedTables;
                $drySummary = [];
                foreach ($tables as $table) {
                    if (Schema::connection($source)->hasTable($table)) {
                        $count = $sourceConn->table($table)->count();
                        $drySummary[] = ['table' => $table, 'source_rows' => $count, 'status' => 'READY'];
                    } else {
                        $drySummary[] = ['table' => $table, 'source_rows' => 'N/A', 'status' => 'NOT_IN_SOURCE'];
                    }
                }
                $this->table(['Table', 'Source Records', 'Status'], $drySummary);
                $this->info("Dry run complete. No files or records were changed.");
                return 0;
            }

            if (!$force) {
                if (!$this->confirm("This will import data from [{$source}] into a new staging SQLite file and atomically replace [{$targetPath}]. Proceed?", false)) {
                    $this->warn("Migration aborted by user.");
                    return 0;
                }
            }

            // 6. Staging File Setup
            $customStaging = $this->option('staging-path');
            if (!empty($customStaging)) {
                $stagingPath = $customStaging;
                if (File::exists($stagingPath) && File::size($stagingPath) > 0) {
                    $this->error("Staging target file [{$stagingPath}] already exists and is non-empty. Refusing to overwrite.");
                    return 1;
                }
            } else {
                $stagingPath = $targetDir . '/staging_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.sqlite';
            }

            $this->info("Creating fresh staging SQLite database: {$stagingPath}");
            File::put($stagingPath, '');
            @chmod($stagingPath, 0660);

            // Configure dynamic staging connection
            Config::set('database.connections.sqlite_staging', [
                'driver' => 'sqlite',
                'database' => $stagingPath,
                'prefix' => '',
                'foreign_key_constraints' => false,
                'busy_timeout' => 5000,
                'journal_mode' => 'WAL',
                'synchronous' => 'NORMAL',
            ]);
            DB::purge('sqlite_staging');

            $stagingConn = DB::connection('sqlite_staging');
            $stagingConn->getPdo();

            // 7. Run Migrations on Staging Target to establish complete schema
            $this->info("Migrating schema on staging SQLite database...");
            $migrationRepo = new \Illuminate\Database\Migrations\DatabaseMigrationRepository(app('db'), 'migrations');
            $migrationRepo->setSource('sqlite_staging');
            if (! $migrationRepo->repositoryExists()) {
                $migrationRepo->createRepository();
            }
            $stagingMigrator = new \Illuminate\Database\Migrations\Migrator($migrationRepo, app('db'), app('files'), app('events'));
            $stagingMigrator->setConnection('sqlite_staging');
            $stagingMigrator->run(database_path('migrations'));

            // 8. Discover and order tables to migrate
            $rawTables = Schema::connection('sqlite_staging')->getTableListing();
            $discoveredTables = [];
            foreach ($rawTables as $t) {
                $cleanName = str_contains($t, '.') ? substr($t, strpos($t, '.') + 1) : $t;
                if ($cleanName !== 'migrations') {
                    $discoveredTables[] = $cleanName;
                }
            }

            $tablesToMigrate = [];
            foreach ($this->orderedTables as $tbl) {
                if (in_array($tbl, $discoveredTables, true)) {
                    $tablesToMigrate[] = $tbl;
                }
            }
            foreach ($discoveredTables as $tbl) {
                if (!in_array($tbl, $tablesToMigrate, true)) {
                    $tablesToMigrate[] = $tbl;
                }
            }

            $tablesToMigrate = array_values(array_filter($tablesToMigrate, fn($t) => !in_array($t, $skippedTables, true)));
            $this->info(sprintf("Migrating %d tables into staging database...", count($tablesToMigrate)));

            // 9. Execute copy with consistent MySQL transaction and deterministic ordering
            $stagingConn->statement('PRAGMA foreign_keys = OFF');
            $stagingConn->statement('PRAGMA synchronous = OFF');

            $startedTx = false;
            if ($sourceConn->transactionLevel() === 0) {
                try {
                    $sourceConn->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
                } catch (\Throwable) {}
                $sourceConn->beginTransaction();
                $startedTx = true;
            }

            $summary = [];
            try {
                foreach ($tablesToMigrate as $table) {
                    if (!Schema::connection($source)->hasTable($table)) {
                        $this->warn("Table [{$table}] does not exist in source. Skipping.");
                        $summary[] = [
                            'table' => $table,
                            'source_rows' => 'N/A',
                            'target_rows' => $stagingConn->table($table)->count(),
                            'status' => 'SKIPPED_NOT_IN_SOURCE',
                        ];
                        continue;
                    }

                    $sourceCount = $sourceConn->table($table)->count();
                    $hasId = Schema::connection('sqlite_staging')->hasColumn($table, 'id');
                    $hasKey = Schema::connection('sqlite_staging')->hasColumn($table, 'key');

                    // Clear any baseline seed rows created by migrations in staging
                    $stagingConn->table($table)->delete();

                    if ($sourceCount > 0) {
                        if ($hasId) {
                            // Keyset pagination on id
                            $lastId = null;
                            while (true) {
                                $q = $sourceConn->table($table)->orderBy('id', 'asc')->limit($chunkSize);
                                if ($lastId !== null) {
                                    $q->where('id', '>', $lastId);
                                }
                                $rows = $q->get();
                                if ($rows->isEmpty()) {
                                    break;
                                }

                                $data = array_map(fn($r) => (array) $r, $rows->all());
                                $stagingConn->table($table)->insert($data);
                                $lastId = $rows->last()->id;
                            }
                        } elseif ($hasKey) {
                            // Keyset pagination on key
                            $lastKey = null;
                            while (true) {
                                $q = $sourceConn->table($table)->orderBy('key', 'asc')->limit($chunkSize);
                                if ($lastKey !== null) {
                                    $q->where('key', '>', $lastKey);
                                }
                                $rows = $q->get();
                                if ($rows->isEmpty()) {
                                    break;
                                }

                                $data = array_map(fn($r) => (array) $r, $rows->all());
                                $stagingConn->table($table)->insert($data);
                                $lastKey = $rows->last()->key;
                            }
                        } else {
                            // Chunk by offset
                            for ($offset = 0; $offset < $sourceCount; $offset += $chunkSize) {
                                $rows = $sourceConn->table($table)->offset($offset)->limit($chunkSize)->get();
                                $data = array_map(fn($r) => (array) $r, $rows->all());
                                if (!empty($data)) {
                                    $stagingConn->table($table)->insert($data);
                                }
                            }
                        }
                    }

                    $stagingCount = $stagingConn->table($table)->count();
                    if ($sourceCount !== $stagingCount) {
                        throw new \RuntimeException("Row count mismatch on table [{$table}]: source has {$sourceCount}, staging has {$stagingCount}.");
                    }

                    // Verify min/max ID
                    if ($hasId && $sourceCount > 0) {
                        $sourceMin = $sourceConn->table($table)->min('id');
                        $sourceMax = $sourceConn->table($table)->max('id');
                        $stagingMin = $stagingConn->table($table)->min('id');
                        $stagingMax = $stagingConn->table($table)->max('id');

                        if ($sourceMin != $stagingMin || $sourceMax != $stagingMax) {
                            throw new \RuntimeException("Primary key range mismatch on table [{$table}]: source [{$sourceMin}..{$sourceMax}] != staging [{$stagingMin}..{$stagingMax}].");
                        }
                    }

                    $summary[] = [
                        'table' => $table,
                        'source_rows' => $sourceCount,
                        'target_rows' => $stagingCount,
                        'status' => 'OK',
                    ];
                }

                if ($startedTx) {
                    $sourceConn->commit();
                }
            } catch (\Throwable $copyEx) {
                if ($startedTx) {
                    $sourceConn->rollBack();
                }
                throw $copyEx;
            }

            // 10. Re-enable foreign keys and verify integrity on staging
            $stagingConn->statement('PRAGMA foreign_keys = ON');
            $stagingConn->statement('PRAGMA synchronous = NORMAL');

            $this->info("Verifying foreign key constraints on staging database...");
            $fkViolations = $stagingConn->select('PRAGMA foreign_key_check');
            if (!empty($fkViolations)) {
                $this->table(['table', 'rowid', 'parent_table', 'fkid'], array_map(fn($v) => (array) $v, $fkViolations));
                throw new \RuntimeException("PRAGMA foreign_key_check found violations in staging SQLite database.");
            }
            $this->info("PRAGMA foreign_key_check passed with 0 violations.");

            // Verify integrity
            $integrity = $stagingConn->select('PRAGMA integrity_check');
            $integrityStatus = $integrity[0]->integrity_check ?? 'failed';
            if ($integrityStatus !== 'ok') {
                throw new \RuntimeException("PRAGMA integrity_check failed on staging SQLite database: {$integrityStatus}");
            }
            $this->info("PRAGMA integrity_check passed: ok.");

            // 11. Checkpoint WAL, optimize, and close staging connection
            $this->info("Checkpointing and closing staging SQLite database...");
            $stagingConn->statement('PRAGMA wal_checkpoint(TRUNCATE)');
            $stagingConn->statement('VACUUM');
            $stagingConn->statement('ANALYZE');

            DB::disconnect('sqlite_staging');
            DB::purge('sqlite_staging');

            // 12. Atomic Promotion: Backup active target if it exists, then rename staging to target
            if (File::exists($targetPath)) {
                $rollbackBackup = $targetDir . '/backup_before_cutover_' . now()->format('Ymd_His') . '.sqlite';
                $this->info("Backing up currently active SQLite database to: {$rollbackBackup}");
                File::copy($targetPath, $rollbackBackup);
            }

            $this->info("Atomically promoting staging database to active destination: {$targetPath}");
            if (!rename($stagingPath, $targetPath)) {
                throw new \RuntimeException("Failed to rename staging file [{$stagingPath}] to [{$targetPath}].");
            }

            // Clean up any remaining staging WAL/SHM sidecars
            if (File::exists($stagingPath . '-wal')) {
                File::delete($stagingPath . '-wal');
            }
            if (File::exists($stagingPath . '-shm')) {
                File::delete($stagingPath . '-shm');
            }

            $this->newLine();
            $this->table(['Table', 'Source Records', 'Staging Records', 'Status'], $summary);
            $this->info("SQLite migration and atomic promotion completed successfully with 0 errors.");
            return 0;

        } catch (\Throwable $e) {
            $this->error("Migration failed: " . $e->getMessage());

            // Quarantine or remove broken staging file
            if (isset($stagingPath) && File::exists($stagingPath)) {
                $failedPath = $stagingPath . '.failed';
                @rename($stagingPath, $failedPath);
                $this->warn("Broken staging database quarantined at: {$failedPath}");
                // Clean sidecars
                if (File::exists($stagingPath . '-wal')) {
                    @File::delete($stagingPath . '-wal');
                }
                if (File::exists($stagingPath . '-shm')) {
                    @File::delete($stagingPath . '-shm');
                }
            }

            $this->warn("The active database [{$targetPath}] and MySQL source were left completely untouched.");
            return 1;
        } finally {
            if (File::exists($lockFile)) {
                File::delete($lockFile);
            }
        }
    }
}
