<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InternalDeploymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\RateLimiter::clear('deploy-limit');
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    protected function tearDown(): void
    {
        putenv('DEPLOYMENT_TOKEN');
        config(['app.deployment_token' => null]);
        parent::tearDown();
    }

    public function test_get_deploy_page_renders_cleanly(): void
    {
        $response = $this->get('/internal/deploy');

        $response->assertStatus(200);
        $response->assertSee('Hydrox Deployment');
        $response->assertSee('Deployment Token');
        $response->assertSee('Run Deployment');
    }

    public function test_post_deploy_fails_if_token_not_configured(): void
    {
        putenv('DEPLOYMENT_TOKEN');
        config(['app.deployment_token' => null]);

        $response = $this->post('/internal/deploy', ['token' => 'any_token']);

        $response->assertStatus(500);
        $response->assertSee('DEPLOYMENT_TOKEN is not configured');
    }

    public function test_post_deploy_rejects_missing_or_invalid_token(): void
    {
        putenv('DEPLOYMENT_TOKEN=super_secret_token_12345');
        config(['app.deployment_token' => 'super_secret_token_12345']);

        // Missing token
        $response = $this->post('/internal/deploy', []);
        $response->assertStatus(403);
        $response->assertSee('Unauthorized Access');

        // Invalid token
        $response = $this->post('/internal/deploy', ['token' => 'wrong_token']);
        $response->assertStatus(403);
        $response->assertSee('Unauthorized Access');
    }

    public function test_post_deploy_succeeds_with_valid_post_token(): void
    {
        putenv('DEPLOYMENT_TOKEN=super_secret_token_12345');
        config(['app.deployment_token' => 'super_secret_token_12345']);

        // Mock Artisan command call to avoid running full migrations in unit test
        Artisan::shouldReceive('call')
            ->once()
            ->with('hydrox:deploy')
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn("Mock deployment output\nDatabase migrations completed successfully.\nsuper_secret_token_12345");

        $response = $this->post('/internal/deploy', ['token' => 'super_secret_token_12345']);

        $response->assertStatus(200);
        $response->assertSee('Deployment Successful');
        $response->assertSee('Exit Code 0');
        $response->assertSee('Database migrations completed successfully.');
        // Sensitive token should be sanitized / redacted
        $response->assertDontSee('super_secret_token_12345');
        $response->assertSee('[REDACTED]');
    }

    public function test_post_deploy_succeeds_with_header_token(): void
    {
        putenv('DEPLOYMENT_TOKEN=super_secret_token_12345');
        config(['app.deployment_token' => 'super_secret_token_12345']);

        Artisan::shouldReceive('call')
            ->once()
            ->with('hydrox:deploy')
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Deployment via header success');

        $response = $this->withHeader('X-Deployment-Token', 'super_secret_token_12345')
            ->post('/internal/deploy');

        $response->assertStatus(200);
        $response->assertSee('Deployment Successful');
        $response->assertSee('Deployment via header success');
    }
}
