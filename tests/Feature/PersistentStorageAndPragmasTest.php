<?php

namespace Tests\Feature;

use App\Actions\CreateBookingLeadAction;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PersistentStorageAndPragmasTest extends TestCase
{
    use RefreshDatabase;

    private string $tempStoragePrivate;
    private string $tempStoragePublic;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake();

        $this->tempStoragePrivate = sys_get_temp_dir() . '/hydrox_private_' . uniqid();
        $this->tempStoragePublic = sys_get_temp_dir() . '/hydrox_public_' . uniqid();

        File::makeDirectory($this->tempStoragePrivate, 0750, true, true);
        File::makeDirectory($this->tempStoragePublic, 0755, true, true);

        // Configure persistent roots
        Config::set('filesystems.disks.local.root', $this->tempStoragePrivate);
        Config::set('filesystems.disks.public.root', $this->tempStoragePublic);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->tempStoragePrivate)) {
            File::deleteDirectory($this->tempStoragePrivate);
        }
        if (File::isDirectory($this->tempStoragePublic)) {
            File::deleteDirectory($this->tempStoragePublic);
        }
        parent::tearDown();
    }

    public function test_booking_photos_are_stored_in_configured_persistent_local_root(): void
    {
        $file = UploadedFile::fake()->image('test_job_photo.jpg');

        $action = app(CreateBookingLeadAction::class);
        $booking = $action->execute([
            'customer_name' => 'Storage Tester',
            'email' => 'storage@example.com',
            'phone' => '0412345678',
            'service' => 'Commercial Cleaning',
        ], [$file]);

        $this->assertDatabaseHas(Booking::class, ['reference' => $booking->reference]);
        $photo = $booking->photos()->first();
        $this->assertNotNull($photo);

        // Verify the photo file was written directly inside our configured persistent root
        $expectedPhotoPath = $this->tempStoragePrivate . '/' . $photo->path;
        $this->assertTrue(File::exists($expectedPhotoPath), "Photo must exist at persistent root: {$expectedPhotoPath}");
        $this->assertGreaterThan(0, File::size($expectedPhotoPath));
    }

    public function test_sqlite_pragmas_are_actually_applied_on_connection(): void
    {
        $tempDb = sys_get_temp_dir() . '/pragma_test_' . uniqid() . '.sqlite';
        File::put($tempDb, '');

        Config::set('database.connections.pragma_check', [
            'driver' => 'sqlite',
            'database' => $tempDb,
            'foreign_key_constraints' => true,
            'busy_timeout' => 5000,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
        ]);

        $pdo = DB::connection('pragma_check')->getPdo();

        $journalMode = strtolower((string) $pdo->query('PRAGMA journal_mode')->fetchColumn());
        $synchronous = (int) $pdo->query('PRAGMA synchronous')->fetchColumn();
        $busyTimeout = (int) $pdo->query('PRAGMA busy_timeout')->fetchColumn();
        $foreignKeys = (int) $pdo->query('PRAGMA foreign_keys')->fetchColumn();

        $this->assertSame('wal', $journalMode);
        $this->assertSame(1, $synchronous); // 1 = NORMAL
        $this->assertSame(5000, $busyTimeout);
        $this->assertSame(1, $foreignKeys);

        File::delete($tempDb);
        if (File::exists($tempDb . '-wal')) File::delete($tempDb . '-wal');
        if (File::exists($tempDb . '-shm')) File::delete($tempDb . '-shm');
    }

    public function test_hydrox_deploy_does_not_invoke_db_seed(): void
    {
        $consoleRoutes = File::get(base_path('routes/console.php'));
        $this->assertStringNotContainsString("'db:seed'", $consoleRoutes);
        $this->assertStringNotContainsString('"db:seed"', $consoleRoutes);
    }
}
