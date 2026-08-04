<?php

namespace Tests\Feature;

use App\Models\SubcontractorOnboarding;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\SubcontractorDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubcontractorOnboardingDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_requires_resume_and_any_one_citizenship_document(): void
    {
        Storage::fake('public');

        $this->post(route('subcontractor-onboardings.store'), $this->payload())
            ->assertSessionHasErrors(['resume']);
        $this->post(route('subcontractor-onboardings.store'), $this->payload([
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors(['citizenship_evidence']);

        $response = $this->post(route('subcontractor-onboardings.store'), $this->payload([
            'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            'birth_certificate' => UploadedFile::fake()->create('birth.pdf', 100, 'application/pdf'),
        ]));

        $response->assertRedirect(route('login'));
        $onboarding = SubcontractorOnboarding::firstOrFail();
        $this->assertTrue($onboarding->hasCurrentDocument('resume'));
        $this->assertTrue($onboarding->hasCurrentDocument('birth_certificate'));
        $this->assertCount(1, SystemNotification::all());
    }

    public function test_permanent_resident_requires_passport_and_residency_evidence(): void
    {
        Storage::fake('public');
        $payload = $this->payload([
            'residency_status' => 'Permanent Resident',
            'resume' => UploadedFile::fake()->create('resume.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $this->post(route('subcontractor-onboardings.store'), $payload)
            ->assertSessionHasErrors(['passport', 'permanent_residency_evidence']);
    }

    public function test_replacement_creates_history_and_archive_preserves_file(): void
    {
        Storage::fake('public');
        $onboarding = SubcontractorOnboarding::create($this->modelPayload());
        $service = app(SubcontractorDocumentService::class);
        $first = $service->addForOnboarding($onboarding, 'resume', UploadedFile::fake()->create('first.pdf', 50, 'application/pdf'));
        $second = $service->addForOnboarding($onboarding, 'resume', UploadedFile::fake()->create('second.pdf', 60, 'application/pdf'), null, 'Updated experience');

        $this->assertFalse($first->fresh()->is_current);
        $this->assertSame(2, $second->version_number);
        $this->assertSame('Updated experience', $second->replacement_reason);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $this->actingAs($admin)->delete(route('subcontractor-document-versions.archive', $second))->assertRedirect();
        $this->assertNotNull($second->fresh()->archived_at);
        Storage::disk('public')->assertExists($second->storage_path);
    }

    public function test_document_version_routes_require_authentication_and_review_is_audited(): void
    {
        Storage::fake('public');
        $onboarding = SubcontractorOnboarding::create($this->modelPayload());
        $version = app(SubcontractorDocumentService::class)->addForOnboarding(
            $onboarding, 'resume', UploadedFile::fake()->create('resume.pdf', 50, 'application/pdf')
        );

        $this->get(route('subcontractor-document-versions.download', $version))->assertRedirect(route('login'));

        $admin = User::create(['name' => 'Reviewer', 'email' => 'reviewer@example.com', 'password' => 'password']);
        $this->actingAs($admin)->patch(route('subcontractor-document-versions.update', $version), [
            'review_status' => 'Approved',
            'admin_notes' => 'Verified.',
        ])->assertRedirect();

        $version->refresh();
        $this->assertSame('Approved', $version->review_status);
        $this->assertSame($admin->id, $version->reviewed_by);
        $this->assertNotNull($version->reviewed_at);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'abn' => '12345678901', 'business_structure' => 'Sole trader', 'first_name' => 'Sam',
            'last_name' => 'Citizen', 'email' => 'sam@example.com', 'mobile' => '0400000000',
            'business_address' => '1 Test Street', 'residency_status' => 'Australian Citizen',
            'available_hours' => 'Monday to Friday', 'bank_details' => 'Test account', 'gst_registered' => '0',
        ], $overrides);
    }

    private function modelPayload(): array
    {
        return $this->payload([
            'status' => 'Pending Review', 'full_name' => 'Sam Citizen', 'phone' => '0400000000',
            'resume' => null, 'citizenship_evidence' => null,
        ]);
    }
}
