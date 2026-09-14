<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MigrateMysqlToSqliteTest extends TestCase
{
    private string $tempDir;
    private string $sourceDb;
    private string $targetDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/hydrox_test_' . uniqid();
        File::makeDirectory($this->tempDir, 0750, true, true);
        $this->sourceDb = $this->tempDir . '/source.sqlite';
        $this->targetDb = $this->tempDir . '/database.sqlite';

        File::put($this->sourceDb, '');

        // Configure source database connection to point to our test source file
        Config::set('database.connections.test_source', [
            'driver' => 'sqlite',
            'database' => $this->sourceDb,
            'username' => 'test_user',
            'password' => 'test_pass',
            'foreign_key_constraints' => true,
        ]);

        // Run migrations on test_source to create schema using an isolated migrator
        $migrationRepo = new \Illuminate\Database\Migrations\DatabaseMigrationRepository(app('db'), 'migrations');
        $migrationRepo->setSource('test_source');
        if (! $migrationRepo->repositoryExists()) {
            $migrationRepo->createRepository();
        }
        $testMigrator = new \Illuminate\Database\Migrations\Migrator($migrationRepo, app('db'), app('files'), app('events'));
        $testMigrator->setConnection('test_source');
        $testMigrator->run(database_path('migrations'));
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_fails_closed_when_source_credentials_are_missing(): void
    {
        Config::set('database.connections.empty_source', [
            'driver' => 'mysql',
            'database' => '',
            'username' => '',
        ]);

        $exitCode = Artisan::call('hydrox:migrate-to-sqlite', [
            '--source' => 'empty_source',
            '--target-path' => $this->targetDb,
        ]);

        $this->assertSame(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Missing required source credentials', $output);
        $this->assertFalse(File::exists($this->targetDb));
    }

    public function test_dry_run_does_not_create_target_or_staging_files(): void
    {
        $exitCode = Artisan::call('hydrox:migrate-to-sqlite', [
            '--source' => 'test_source',
            '--target-path' => $this->targetDb,
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('DRY RUN', $output);
        $this->assertFalse(File::exists($this->targetDb));

        // Ensure no staging files were created in tempDir (only source.sqlite)
        $files = File::files($this->tempDir);
        $this->assertCount(1, $files);
        $this->assertSame('source.sqlite', $files[0]->getFilename());
    }

    public function test_refuses_if_staging_target_is_non_empty(): void
    {
        $stagingPath = $this->tempDir . '/custom_staging.sqlite';
        File::put($stagingPath, 'non-empty content');

        $exitCode = Artisan::call('hydrox:migrate-to-sqlite', [
            '--source' => 'test_source',
            '--target-path' => $this->targetDb,
            '--staging-path' => $stagingPath,
            '--force' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('already exists and is non-empty. Refusing to overwrite', Artisan::output());
    }

    public function test_refuses_if_concurrent_lock_file_exists(): void
    {
        $lockFile = $this->tempDir . '/.sqlite_migration.lock';
        File::put($lockFile, json_encode(['pid' => 12345, 'started_at' => now()->toIso8601String()]));

        $exitCode = Artisan::call('hydrox:migrate-to-sqlite', [
            '--source' => 'test_source',
            '--target-path' => $this->targetDb,
            '--force' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Migration is locked: another import is currently in progress', Artisan::output());
    }

    public function test_active_database_untouched_and_staging_quarantined_on_mid_import_failure(): void
    {
        // 1. Create an existing active SQLite target with a real booking
        File::put($this->targetDb, '');
        Config::set('database.connections.test_target', [
            'driver' => 'sqlite',
            'database' => $this->targetDb,
            'foreign_key_constraints' => true,
        ]);
        Artisan::call('migrate', ['--database' => 'test_target', '--force' => true]);
        DB::connection('test_target')->table('bookings')->insert([
            'reference' => 'HYD-ACTIVE-KEEP-ME',
            'source' => 'Original DB',
            'status' => 'processing',
            'customer_name' => 'Original Customer',
            'email' => 'original@example.com',
            'phone' => '0400000000',
            'service' => 'General Cleaning',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $initialCount = DB::connection('test_target')->table('bookings')->count();
        $this->assertSame(1, $initialCount);

        // 2. Insert an orphaned foreign key into test_source to guarantee PRAGMA foreign_key_check failure
        DB::connection('test_source')->statement('PRAGMA foreign_keys = OFF');
        DB::connection('test_source')->table('booking_photos')->insert([
            'booking_id' => 999999, // Non-existent parent booking
            'path' => 'booking-photos/999999/orphaned.jpg',
            'original_name' => 'orphaned.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::connection('test_source')->statement('PRAGMA foreign_keys = ON');

        $stagingPath = $this->tempDir . '/staging_fail.sqlite';
        $exitCode = Artisan::call('hydrox:migrate-to-sqlite', [
            '--source' => 'test_source',
            '--target-path' => $this->targetDb,
            '--staging-path' => $stagingPath,
            '--force' => true,
        ]);

        // 3. Verify failure, quarantine, and active DB integrity
        $this->assertSame(1, $exitCode);
        
        // Active database must NOT be touched or altered!
        $this->assertTrue(File::exists($this->targetDb));
        $activeRecord = DB::connection('test_target')->table('bookings')->where('reference', 'HYD-ACTIVE-KEEP-ME')->first();
        $this->assertNotNull($activeRecord, 'Active database record must remain completely intact after failed import');
        $this->assertSame(1, DB::connection('test_target')->table('bookings')->count());

        // Staging file was quarantined as .failed
        $this->assertTrue(File::exists($stagingPath . '.failed'));
    }

    public function test_deterministic_copy_and_atomic_promotion(): void
    {
        // Populate test source directly with non-consecutive IDs
        DB::connection('test_source')->table('bookings')->insert([
            'id' => 10,
            'reference' => 'HYD-20260914-A',
            'source' => 'hydrox.au Website',
            'status' => 'processing',
            'customer_name' => 'Alice First',
            'email' => 'alice@example.com',
            'phone' => '0400111222',
            'service' => 'Commercial Cleaning',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('test_source')->table('bookings')->insert([
            'id' => 25,
            'reference' => 'HYD-20260914-B',
            'source' => 'hydrox.au Website',
            'status' => 'processing',
            'customer_name' => 'Bob Second',
            'email' => 'bob@example.com',
            'phone' => '0400333444',
            'service' => 'Residential Cleaning',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $exitCode = Artisan::call('hydrox:migrate-to-sqlite', [
            '--source' => 'test_source',
            '--target-path' => $this->targetDb,
            '--chunk' => 1, // force keyset chunking
            '--force' => true,
        ]);

        $output = Artisan::output();
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('PRAGMA foreign_key_check passed with 0 violations', $output);
        $this->assertStringContainsString('PRAGMA integrity_check passed: ok', $output);
        $this->assertStringContainsString('Atomically promoting staging database', $output);

        $this->assertTrue(File::exists($this->targetDb));

        // Connect and verify promoted database records
        Config::set('database.connections.promoted_check', [
            'driver' => 'sqlite',
            'database' => $this->targetDb,
            'foreign_key_constraints' => true,
        ]);

        $records = DB::connection('promoted_check')->table('bookings')->orderBy('id')->get();
        $this->assertCount(2, $records);
        $this->assertSame(10, $records[0]->id);
        $this->assertSame('HYD-20260914-A', $records[0]->reference);
        $this->assertSame(25, $records[1]->id);
        $this->assertSame('HYD-20260914-B', $records[1]->reference);
    }

    public function test_maintenance_route_get_is_read_only_form_and_does_not_mutate(): void
    {
        Config::set('app.sqlite_migration_enabled', true);
        Config::set('app.internal_maintenance_token', 'Secret123');
        Config::set('database.connections.sqlite.database', $this->targetDb);

        $response = $this->get('/internal/maintenance/migrate-sqlite');
        $response->assertOk();
        $response->assertSee('<form method="POST"', false);
        $response->assertSee('name="_token"', false); // CSRF token present
        $response->assertSee('type="password"', false); // password input for token

        // Verify target database was NOT created or modified by GET
        $this->assertFalse(File::exists($this->targetDb));
    }

    public function test_maintenance_route_rejects_query_string_token(): void
    {
        Config::set('app.sqlite_migration_enabled', true);
        Config::set('app.internal_maintenance_token', 'Secret123');

        $response = $this->postJson('/internal/maintenance/migrate-sqlite?token=Secret123');
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Tokens passed in query string URLs are strictly prohibited. Pass token in POST body or X-Maintenance-Token header.',
        ]);
    }

    public function test_maintenance_route_returns_404_when_disabled(): void
    {
        Config::set('app.sqlite_migration_enabled', false);

        $response = $this->get('/internal/maintenance/migrate-sqlite');
        $response->assertNotFound();

        $postResponse = $this->postJson('/internal/maintenance/migrate-sqlite', ['token' => 'any']);
        $postResponse->assertNotFound();
    }

    public function test_maintenance_route_returns_401_when_token_invalid(): void
    {
        Config::set('app.sqlite_migration_enabled', true);
        Config::set('app.internal_maintenance_token', 'CorrectSecretToken');

        $response = $this->postJson('/internal/maintenance/migrate-sqlite', [
            'token' => 'WrongSecretToken',
        ]);
        $response->assertStatus(401);
        $response->assertJson(['status' => 'error', 'message' => 'Unauthorized.']);
    }

    public function test_maintenance_route_dry_run_via_post_succeeds(): void
    {
        Config::set('app.sqlite_migration_enabled', true);
        Config::set('app.internal_maintenance_token', 'ValidSecretToken');
        Config::set('database.connections.sqlite.database', $this->targetDb);

        // Pre-configure mysql_source with valid source credentials pointing to test source file
        Config::set('database.connections.mysql_source', [
            'driver' => 'sqlite',
            'database' => $this->sourceDb,
            'username' => 'test_user',
            'password' => 'test_pass',
            'foreign_key_constraints' => false,
        ]);

        $response = $this->postJson('/internal/maintenance/migrate-sqlite', [
            'token' => 'ValidSecretToken',
            'dry_run' => true,
        ]);

        $response->assertStatus(200);
        $this->assertSame('success', $response->json('status'));
        $this->assertTrue($response->json('dry_run'));
        $this->assertStringContainsString('DRY RUN', $response->json('output'));
        $this->assertFalse(File::exists($this->targetDb));
    }
}
