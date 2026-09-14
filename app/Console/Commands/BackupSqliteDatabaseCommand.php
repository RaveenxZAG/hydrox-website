<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupSqliteDatabaseCommand extends Command
{
    protected $signature = 'hydrox:backup-sqlite
        {--destination= : Destination directory for backups}
        {--keep=14 : Number of days of backups to retain}
        {--compress : Compress the backup file with gzip}';

    protected $description = 'Create a consistent, online SQLite database backup using VACUUM INTO and prune old backups';

    public function handle(): int
    {
        $dbConfig = config('database.connections.sqlite');
        $dbPath = $dbConfig['database'] ?? database_path('database.sqlite');

        if ($dbPath === ':memory:' || empty($dbPath)) {
            $this->error('Cannot backup an in-memory or empty SQLite database.');
            return 1;
        }

        if (!File::exists($dbPath)) {
            $this->error("SQLite database file not found at: {$dbPath}");
            return 1;
        }

        // Determine destination directory
        $destDir = $this->option('destination');
        if (empty($destDir)) {
            $defaultPersistent = '/home/hydro851/persistent/hydrox-website/backups';
            $destDir = File::isDirectory('/home/hydro851/persistent/hydrox-website')
                ? $defaultPersistent
                : database_path('backups');
        }

        if (!File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0750, true, true);
        }

        $timestamp = now()->format('Ymd_His');
        $filename = "backup_hydrox_website_{$timestamp}.sqlite";
        $targetPath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $this->info("Creating consistent backup of [{$dbPath}] to [{$targetPath}]...");

        try {
            // Attempt atomic VACUUM INTO via SQLite
            $escapedPath = str_replace("'", "''", $targetPath);
            DB::connection('sqlite')->statement("VACUUM INTO '{$escapedPath}'");
            $this->info("Database successfully backed up using SQLite VACUUM INTO.");
        } catch (\Throwable $e) {
            $this->warn("VACUUM INTO failed ({$e->getMessage()}). Attempting WAL checkpoint fallback...");

            try {
                // Fallback: Checkpoint WAL before copying
                DB::connection('sqlite')->statement('PRAGMA wal_checkpoint(TRUNCATE)');
                File::copy($dbPath, $targetPath);
                $this->info("Database successfully backed up using wal_checkpoint fallback copy.");
            } catch (\Throwable $fallbackEx) {
                $this->error("Backup failed: " . $fallbackEx->getMessage());
                return 1;
            }
        }

        $fileSize = File::size($targetPath);
        $this->info(sprintf("Backup created: %s (%s KB)", $filename, number_format($fileSize / 1024, 2)));

        // Compress if requested
        if ($this->option('compress')) {
            $gzPath = $targetPath . '.gz';
            $content = file_get_contents($targetPath);
            if ($content !== false) {
                file_put_contents($gzPath, gzencode($content, 9));
                File::delete($targetPath);
                $targetPath = $gzPath;
                $filename = basename($gzPath);
                $fileSize = File::size($targetPath);
                $this->info(sprintf("Compressed backup: %s (%s KB)", $filename, number_format($fileSize / 1024, 2)));
            }
        }

        // Prune older backups
        $keepDays = (int) $this->option('keep');
        if ($keepDays > 0) {
            $threshold = now()->subDays($keepDays)->timestamp;
            $files = File::files($destDir);
            $prunedCount = 0;

            foreach ($files as $file) {
                $fileBase = $file->getFilename();
                if (str_starts_with($fileBase, 'backup_hydrox_website_') && $file->getMTime() < $threshold) {
                    File::delete($file->getRealPath());
                    $prunedCount++;
                }
            }

            if ($prunedCount > 0) {
                $this->info("Pruned {$prunedCount} backup(s) older than {$keepDays} days.");
            }
        }

        $this->info("Backup procedure completed successfully.");
        return 0;
    }
}
