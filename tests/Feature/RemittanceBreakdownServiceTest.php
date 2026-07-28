<?php

namespace Tests\Feature;

use App\Models\StaffInvoiceSubmission;
use App\Models\StaffMember;
use App\Services\RemittanceBreakdownService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RemittanceBreakdownServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('staff_members', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->boolean('active')->default(true);
            $table->string('staff_status')->nullable();
            $table->timestamps();
        });
        Schema::create('staff_invoice_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_member_id');
            $table->date('invoice_period');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('approved_total', 12, 2)->nullable();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
        Schema::create('staff_invoice_remittance_breakdowns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_invoice_submission_id')->unique();
            $table->foreignId('staff_member_id');
            $table->decimal('approved_total', 12, 2);
            $table->decimal('fuel_percentage', 7, 4);
            $table->decimal('fuel_amount', 12, 2);
            $table->decimal('tools_materials_percentage', 7, 4);
            $table->decimal('tools_materials_amount', 12, 2);
            $table->decimal('tools_split_percentage', 7, 4)->nullable();
            $table->decimal('tools_amount', 12, 2)->nullable();
            $table->decimal('materials_split_percentage', 7, 4)->nullable();
            $table->decimal('materials_amount', 12, 2)->nullable();
            $table->decimal('labour_percentage', 7, 4);
            $table->decimal('labour_amount', 12, 2);
            $table->timestamp('generated_at');
            $table->string('generation_status');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('staff_invoice_remittance_breakdowns');
        Schema::dropIfExists('staff_invoice_submissions');
        Schema::dropIfExists('staff_members');

        parent::tearDown();
    }

    public function test_exactly_five_thousand_keeps_tools_and_materials_combined_and_balances_to_the_cent(): void
    {
        $invoice = $this->invoice('5000.00', '2026-07-01');
        $breakdown = app(RemittanceBreakdownService::class)->resolve($invoice);

        $this->assertNull($breakdown->tools_amount);
        $this->assertNull($breakdown->materials_amount);
        $this->assertSame(500000, $this->cents($breakdown->labour_amount) + $this->cents($breakdown->fuel_amount) + $this->cents($breakdown->tools_materials_amount));
        $this->assertGreaterThanOrEqual(80.00, (float) $breakdown->labour_percentage);
        $this->assertGreaterThan($this->cents($breakdown->fuel_amount), $this->cents($breakdown->labour_amount));
        $this->assertGreaterThan($this->cents($breakdown->tools_materials_amount), $this->cents($breakdown->labour_amount));
    }

    public function test_above_five_thousand_splits_tools_and_materials_and_reuses_the_saved_breakdown(): void
    {
        $invoice = $this->invoice('5000.01', '2026-08-01');
        $service = app(RemittanceBreakdownService::class);
        $first = $service->resolve($invoice);
        $snapshot = $first->fresh()->toArray();
        $second = $service->resolve($invoice->fresh());

        $this->assertNotNull($first->tools_amount);
        $this->assertNotNull($first->materials_amount);
        $this->assertSame($first->id, $second->id);
        $this->assertSame($snapshot, $second->toArray());
        $this->assertSame(
            500001,
            $this->cents($first->labour_amount)
                + $this->cents($first->fuel_amount)
                + $this->cents($first->tools_amount)
                + $this->cents($first->materials_amount)
        );
    }

    private function invoice(string $approvedTotal, string $period): StaffInvoiceSubmission
    {
        $staff = StaffMember::create([
            'first_name' => 'Test',
            'last_name' => 'Contractor',
            'email' => 'contractor@example.test',
            'active' => true,
        ]);

        return StaffInvoiceSubmission::create([
            'staff_member_id' => $staff->id,
            'invoice_period' => $period,
            'total_amount' => $approvedTotal,
            'approved_total' => $approvedTotal,
            'original_filename' => 'test.xlsx',
            'storage_path' => 'test.xlsx',
            'status' => 'paid',
            'paid_at' => '2026-07-27 12:00:00',
        ]);
    }

    private function cents(string $amount): int
    {
        [$whole, $fraction] = explode('.', $amount);

        return ((int) $whole * 100) + (int) $fraction;
    }
}
