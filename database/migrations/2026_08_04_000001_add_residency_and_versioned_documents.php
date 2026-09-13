<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
            if (! Schema::hasColumn('subcontractor_onboardings', 'residency_status')) {
                $table->string('residency_status')->nullable()->after('business_address');
            }
            if (! Schema::hasColumn('subcontractor_onboardings', 'visa_type')) {
                $table->string('visa_type')->nullable()->after('residency_status');
            }
            if (! Schema::hasColumn('subcontractor_onboardings', 'visa_expiry_date')) {
                $table->date('visa_expiry_date')->nullable()->after('visa_type');
            }
            if (! Schema::hasColumn('subcontractor_onboardings', 'cover_letter_text')) {
                $table->longText('cover_letter_text')->nullable()->after('experience');
            }
        });

        if (! Schema::hasTable('subcontractor_documents')) {
            Schema::create('subcontractor_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('subcontractor_onboarding_id')->nullable()->constrained('subcontractor_onboardings', indexName: 'subcontractor_docs_onboarding_id_foreign')->cascadeOnDelete();
                $table->foreignId('staff_member_id')->nullable()->constrained('staff_members', indexName: 'subcontractor_docs_staff_id_foreign')->cascadeOnDelete();
                $table->string('category');
                $table->timestamps();
                $table->index(['subcontractor_onboarding_id', 'category'], 'subcontractor_docs_onboarding_category_idx');
                $table->index(['staff_member_id', 'category'], 'subcontractor_docs_staff_category_idx');
            });
        }

        if (! Schema::hasTable('subcontractor_document_versions')) {
            Schema::create('subcontractor_document_versions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('subcontractor_document_id')->constrained('subcontractor_documents', indexName: 'subcontractor_doc_versions_doc_id_foreign')->cascadeOnDelete();
                $table->unsignedInteger('version_number');
                $table->string('original_filename');
                $table->string('storage_disk')->default('local');
                $table->string('storage_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('review_status')->default('Pending Review');
                $table->text('admin_notes')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users', indexName: 'subcontractor_doc_versions_uploaded_by_foreign')->nullOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'subcontractor_doc_versions_reviewed_by_foreign')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('replacement_reason')->nullable();
                $table->boolean('is_current')->default(true)->index('subcontractor_doc_versions_is_current_idx');
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->unique(['subcontractor_document_id', 'version_number'], 'subcontractor_document_version_unique');
            });
        }

        $labels = [
            'public_liability_insurance',
            'workers_compensation_insurance',
            'police_clearance',
            'driver_licence',
            'working_rights',
        ];

        DB::table('subcontractor_onboardings')->orderBy('id')->each(function (object $onboarding) use ($labels): void {
            foreach ($labels as $category) {
                $path = $onboarding->{$category} ?? null;
                if (! $path) {
                    continue;
                }

                $documentId = DB::table('subcontractor_documents')->insertGetId([
                    'subcontractor_onboarding_id' => $onboarding->id,
                    'staff_member_id' => $onboarding->staff_member_id ?? null,
                    'category' => $category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->insertVersion($documentId, $path, 'public', basename($path));
            }
        });

        DB::table('staff_members')->orderBy('id')->each(function (object $staff) use ($labels): void {
            foreach ($labels as $category) {
                $path = $staff->{$category} ?? null;
                if (! $path) {
                    continue;
                }

                $existing = DB::table('subcontractor_document_versions')
                    ->join('subcontractor_documents', 'subcontractor_documents.id', '=', 'subcontractor_document_versions.subcontractor_document_id')
                    ->where('subcontractor_documents.staff_member_id', $staff->id)
                    ->where('subcontractor_document_versions.storage_path', $path)
                    ->exists();
                if ($existing) {
                    continue;
                }

                $documentId = DB::table('subcontractor_documents')->insertGetId([
                    'subcontractor_onboarding_id' => $staff->subcontractor_onboarding_id,
                    'staff_member_id' => $staff->id,
                    'category' => $category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $nameField = $category.'_name';
                $this->insertVersion($documentId, $path, 'local', $staff->{$nameField} ?? basename($path));
            }
        });
    }

    private function insertVersion(int $documentId, string $path, string $disk, string $name): void
    {
        DB::table('subcontractor_document_versions')->insert([
            'subcontractor_document_id' => $documentId,
            'version_number' => 1,
            'original_filename' => $name,
            'storage_disk' => $disk,
            'storage_path' => $path,
            'review_status' => 'Pending Review',
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractor_document_versions');
        Schema::dropIfExists('subcontractor_documents');
        Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
            $table->dropColumn(['residency_status', 'visa_type', 'visa_expiry_date', 'cover_letter_text']);
        });
    }
};
