<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GitCleanlinessTest extends TestCase
{
    public function test_gitignore_ignores_sqlite_and_backups(): void
    {
        $gitignore = File::get(base_path('.gitignore'));

        $this->assertStringContainsString('*.sqlite', $gitignore);
        $this->assertStringContainsString('*.sqlite-wal', $gitignore);
        $this->assertStringContainsString('*.sqlite-shm', $gitignore);
        $this->assertStringContainsString('*.sqlite-journal', $gitignore);
        $this->assertStringContainsString('/backups/', $gitignore);
    }

    public function test_bootstrap_cache_is_ignored_except_gitignore(): void
    {
        $gitignore = File::get(base_path('.gitignore'));

        $this->assertStringContainsString('/bootstrap/cache/*', $gitignore);
        $this->assertStringContainsString('!/bootstrap/cache/.gitignore', $gitignore);
    }

    public function test_google_ads_conversion_tag_token_is_exact(): void
    {
        $viewContent = File::get(resource_path('views/pages/confirmation.blade.php'));

        $this->assertStringContainsString('AW-18428986459/b0SgCPbm6-0cENuI0NNE', $viewContent);
        $this->assertStringNotContainsString('bOSgCPbm6-0cENu10NNE', $viewContent);
    }

    public function test_cpanel_clears_config_in_a_separate_process_before_deployment(): void
    {
        $yaml = File::get(base_path('.cpanel.yml'));
        $script = File::get(base_path('scripts/cpanel-deploy.sh'));
        $this->assertStringContainsString('/bin/bash scripts/cpanel-deploy.sh', $yaml);
        $this->assertStringNotContainsString('bash -lc', $yaml);
        $clear = strpos($script, '"$hydrox_php_bin" artisan config:clear --no-interaction');
        $deploy = strpos($script, '"$hydrox_php_bin" artisan hydrox:deploy --no-interaction');
        $this->assertNotFalse($clear);
        $this->assertNotFalse($deploy);
        $this->assertLessThan($deploy, $clear);
    }
}
