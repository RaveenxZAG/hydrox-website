<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Tests\TestCase;

class BackupSqliteTest extends TestCase
{
    private string $tempDbPath;
    private string $tempBackupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDbPath = sys_get_temp_dir() . '/test_backup_' . uniqid() . '.sqlite';
        $this->tempBackupDir = sys_get_temp_dir() . '/test_backup_dir_' . uniqid();

        File::put($this->tempDbPath, '');
        File::makeDirectory($this->tempBackupDir, 0750, true, true);

        // Configure sqlite connection
        Config::set('database.connections.sqlite.database', $this->tempDbPath);

        // Create a test table and row in sqlite
        DB::connection('sqlite')->statement('CREATE TABLE test_items (id INTEGER PRIMARY KEY, name TEXT)');
        DB::connection('sqlite')->table('test_items')->insert(['name' => 'BackupItem']);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDbPath)) {
            File::delete($this->tempDbPath);
        }
        if (File::isDirectory($this->tempBackupDir)) {
            File::deleteDirectory($this->tempBackupDir);
        }
        parent::tearDown();
    }

    public function test_backup_creates_consistent_sqlite_copy_and_verifies_integrity(): void
    {
        $exitCode = Artisan::call('hydrox:backup-sqlite', [
            '--destination' => $this->tempBackupDir,
            '--keep' => 14,
        ]);

        $this->assertSame(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Backup created', $output);

        $backupFiles = File::files($this->tempBackupDir);
        $this->assertCount(1, $backupFiles);
        $backupPath = $backupFiles[0]->getRealPath();
        $this->assertGreaterThan(0, File::size($backupPath));

        // Direct PDO integrity verification of the backup file
        $backupPdo = new PDO('sqlite:' . $backupPath);
        $integrity = $backupPdo->query('PRAGMA integrity_check')->fetchColumn();
        $this->assertSame('ok', $integrity);

        $stmt = $backupPdo->query('SELECT name FROM test_items WHERE id = 1');
        $this->assertSame('BackupItem', $stmt->fetchColumn());
    }

    public function test_backup_with_compression_and_decompression_integrity(): void
    {
        $exitCode = Artisan::call('hydrox:backup-sqlite', [
            '--destination' => $this->tempBackupDir,
            '--compress' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Compressed backup', $output);

        $backupFiles = File::files($this->tempBackupDir);
        $this->assertCount(1, $backupFiles);
        $gzPath = $backupFiles[0]->getRealPath();
        $this->assertStringEndsWith('.sqlite.gz', $gzPath);

        // Decompress and verify restored SQLite database
        $decompressedContent = gzdecode(File::get($gzPath));
        $this->assertNotFalse($decompressedContent);

        $restoredDbPath = $this->tempBackupDir . '/restored_test.sqlite';
        File::put($restoredDbPath, $decompressedContent);

        $restoredPdo = new PDO('sqlite:' . $restoredDbPath);
        $integrity = $restoredPdo->query('PRAGMA integrity_check')->fetchColumn();
        $this->assertSame('ok', $integrity);

        $stmt = $restoredPdo->query('SELECT name FROM test_items WHERE id = 1');
        $this->assertSame('BackupItem', $stmt->fetchColumn());
    }

    public function test_backup_prunes_older_files(): void
    {
        // Create an old backup file (15 days old)
        $oldFile = $this->tempBackupDir . '/backup_hydrox_website_20260101_000000.sqlite';
        File::put($oldFile, 'old backup data');
        touch($oldFile, now()->subDays(15)->timestamp);

        $exitCode = Artisan::call('hydrox:backup-sqlite', [
            '--destination' => $this->tempBackupDir,
            '--keep' => 14,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertFileDoesNotExist($oldFile);
    }

    public function test_backup_rejects_in_memory_database(): void
    {
        Config::set('database.connections.sqlite.database', ':memory:');

        $exitCode = Artisan::call('hydrox:backup-sqlite', [
            '--destination' => $this->tempBackupDir,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Cannot backup an in-memory', Artisan::output());
    }
}
