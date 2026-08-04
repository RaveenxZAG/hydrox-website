<?php

namespace Tests\Feature;

use App\Mail\CompanyProfileMail;
use App\Models\CompanyProfileEmailDelivery;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\MicrosoftGraphMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CompanyProfileEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_page_and_submit_route_require_authentication(): void
    {
        $this->get('/emails/new')->assertRedirect('/login');
        $this->post('/emails/new', ['recipients' => 'client@example.com'])->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_the_send_page_and_email_menu(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/emails/new')
            ->assertOk()
            ->assertSee('Send new email')
            ->assertSee('Preconfigured template')
            ->assertSee('Hydrox Facility Management | Company Profile &amp; Service Capabilities', false);
    }

    public function test_multiple_mixed_and_duplicate_recipients_receive_separate_emails(): void
    {
        Mail::fake();
        $this->disableMicrosoftGraph();
        $user = $this->createUser();

        $this->actingAs($user)->post('/emails/new', [
            'recipients' => "First@Example.com, second@example.com;\nfirst@example.com",
        ])->assertRedirect(route('emails.create'))
            ->assertSessionHas('status', 'Sent 2 emails.');

        Mail::assertSent(CompanyProfileMail::class, 2);
        Mail::assertSent(CompanyProfileMail::class, fn (CompanyProfileMail $mail): bool => $mail->hasTo('first@example.com'));
        Mail::assertSent(CompanyProfileMail::class, fn (CompanyProfileMail $mail): bool => $mail->hasTo('second@example.com'));

        $this->assertDatabaseHas(CompanyProfileEmailDelivery::class, [
            'recipient' => 'first@example.com',
            'status' => 'sent',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount(CompanyProfileEmailDelivery::class, 2);
    }

    public function test_invalid_and_excessive_recipient_lists_are_rejected(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->from('/emails/new')
            ->post('/emails/new', ['recipients' => 'valid@example.com, not-an-email'])
            ->assertRedirect('/emails/new')
            ->assertSessionHasErrors('recipients');

        $recipients = collect(range(1, 51))->map(fn (int $number): string => "person{$number}@example.com")->join(',');

        $this->actingAs($user)
            ->from('/emails/new')
            ->post('/emails/new', ['recipients' => $recipients])
            ->assertRedirect('/emails/new')
            ->assertSessionHasErrors('recipients');

        $this->assertDatabaseCount(CompanyProfileEmailDelivery::class, 0);
    }

    public function test_email_html_contains_business_details_and_secure_profile_link(): void
    {
        SystemSetting::setValue('business_information', json_encode([
            ...SystemSetting::businessInformation(),
            'company_name' => 'Hydrox Test Company',
            'website' => 'https://hydrox.example',
            'email' => 'hello@hydrox.example',
            'phone' => '0400 000 000',
            'address_line_1' => '10 Test Street',
            'city' => 'Darwin',
            'state' => 'NT',
            'postcode' => '0800',
        ]));

        $html = (new CompanyProfileMail(SystemSetting::businessInformation()))->render();

        $this->assertStringContainsString('Hydrox Test Company', $html);
        $this->assertStringContainsString('hello@hydrox.example', $html);
        $this->assertStringContainsString('10 Test Street', $html);
        $this->assertStringContainsString('href="https://profile.hydrox.au"', $html);
        $this->assertStringContainsString('VIEW OUR COMPANY PROFILE', $html);
        $this->assertStringContainsString('Professional Facility Solutions Built Around Your Organisation', $html);
        $this->assertStringContainsString('REQUEST A FREE QUOTE', $html);
        $this->assertStringContainsString('company-profile-email/hydrox-team-hero.jpg', $html);
    }

    public function test_a_failed_recipient_does_not_stop_remaining_deliveries(): void
    {
        $user = $this->createUser();
        $graph = Mockery::mock(MicrosoftGraphMailService::class);
        $graph->shouldReceive('configured')->times(3)->andReturnTrue();
        $attempt = 0;
        $graph->shouldReceive('send')->times(3)->andReturnUsing(function () use (&$attempt): void {
            $attempt++;

            if ($attempt === 2) {
                throw new RuntimeException('Sensitive provider response that must not be stored');
            }
        });
        $this->app->instance(MicrosoftGraphMailService::class, $graph);

        $this->actingAs($user)->post('/emails/new', [
            'recipients' => 'one@example.com, two@example.com, three@example.com',
        ])->assertRedirect(route('emails.create'))
            ->assertSessionHas('error', 'Sent 2 emails. 1 delivery failed.');

        $this->assertDatabaseCount(CompanyProfileEmailDelivery::class, 3);
        $this->assertDatabaseHas(CompanyProfileEmailDelivery::class, [
            'recipient' => 'two@example.com',
            'status' => 'failed',
            'error_message' => 'Delivery failed via the configured mail provider.',
        ]);
        $this->assertDatabaseMissing(CompanyProfileEmailDelivery::class, [
            'error_message' => 'Sensitive provider response that must not be stored',
        ]);
    }

    public function test_history_is_newest_first_and_displays_the_sender(): void
    {
        $user = $this->createUser('Portal Admin');
        CompanyProfileEmailDelivery::create([
            'user_id' => $user->id,
            'recipient' => 'older@example.com',
            'subject' => CompanyProfileMail::SUBJECT,
            'status' => 'sent',
            'sent_at' => now()->subMinute(),
            'created_at' => now()->subMinute(),
        ]);
        CompanyProfileEmailDelivery::create([
            'user_id' => $user->id,
            'recipient' => 'newer@example.com',
            'subject' => CompanyProfileMail::SUBJECT,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/emails/new');

        $response->assertOk()->assertSee('Portal Admin');
        $this->assertLessThan(
            strpos($response->getContent(), 'older@example.com'),
            strpos($response->getContent(), 'newer@example.com')
        );
    }

    private function disableMicrosoftGraph(): void
    {
        config([
            'services.microsoft_graph.tenant_id' => null,
            'services.microsoft_graph.client_id' => null,
            'services.microsoft_graph.client_secret' => null,
            'services.microsoft_graph.sender' => null,
        ]);
    }

    private function createUser(string $name = 'Test User'): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
