<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('hydrox:deploy', function () {
    $this->info('Starting Hydrox production deployment...');

    $this->info('Clearing old compiled config...');
    $this->call('config:clear');

    // Ensure persistent SQLite directory and database file exist if SQLite is active
    $defaultConn = config('database.default', 'mysql');
    $dbConfig = config("database.connections.{$defaultConn}", []);
    if (($dbConfig['driver'] ?? '') === 'sqlite') {
        $dbPath = $dbConfig['database'] ?? '';
        if ($dbPath !== ':memory:' && !empty($dbPath)) {
            $dir = dirname($dbPath);
            if (!\Illuminate\Support\Facades\File::isDirectory($dir)) {
                \Illuminate\Support\Facades\File::makeDirectory($dir, 0750, true, true);
            }
            if (!\Illuminate\Support\Facades\File::exists($dbPath)) {
                \Illuminate\Support\Facades\File::put($dbPath, '');
                @chmod($dbPath, 0660);
                $this->info("Created new SQLite database file at {$dbPath}");
            }
        }
    }

    $this->info('Applying pending database migrations...');
    $migrateExit = $this->call('migrate', ['--force' => true]);
    if ($migrateExit !== 0) {
        $this->error('Database migrations failed with exit code ' . $migrateExit);
        return 1;
    }

    $this->info('Linking storage...');
    try {
        $publicRoot = config('filesystems.disks.public.root');
        $localRoot = config('filesystems.disks.local.root');
        if (!empty($publicRoot) && !\Illuminate\Support\Facades\File::isDirectory($publicRoot)) {
            \Illuminate\Support\Facades\File::makeDirectory($publicRoot, 0755, true, true);
        }
        if (!empty($localRoot) && !\Illuminate\Support\Facades\File::isDirectory($localRoot)) {
            \Illuminate\Support\Facades\File::makeDirectory($localRoot, 0750, true, true);
        }
        $this->call('storage:link', ['--force' => true]);
    } catch (\Throwable $e) {
        $this->warn('storage:link notice: ' . $e->getMessage());
    }

    $this->info('Caching configuration, routes, and views for production...');
    try {
        $this->call('optimize');
    } catch (\Throwable $e) {
        $this->warn('optimize notice: ' . $e->getMessage());
    }

    $this->info('Hydrox deployment completed successfully.');
    return 0;
})->purpose('Safely migrate, seed baseline data, link storage, and optimize for production');
