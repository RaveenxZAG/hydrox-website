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

    $this->info('Applying pending database migrations...');
    $migrateExit = $this->call('migrate', ['--force' => true]);
    if ($migrateExit !== 0) {
        $this->error('Database migrations failed with exit code ' . $migrateExit);
        return 1;
    }

    $this->info('Seeding required baseline data...');
    $seedExit = $this->call('db:seed', ['--force' => true]);
    if ($seedExit !== 0) {
        $this->error('Database seeding failed with exit code ' . $seedExit);
        return 1;
    }

    $this->info('Linking storage...');
    try {
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
