<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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

    public function test_backup_creates_consistent_sqlite_copy(): void
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
        $this->assertGreaterThan(0, $backupFiles[0]->getSize());
    }

    public function test_backup_with_compression(): void
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
        $this->assertStringEndsWith('.sqlite.gz', $backupFiles[0]->getFilename());
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
