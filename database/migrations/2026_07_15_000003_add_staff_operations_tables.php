<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_members', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_members', 'public_id')) {
                $table->string('public_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('staff_members', 'normalized_email')) {
                $table->string('normalized_email')->nullable()->index()->after('email');
            }
            if (! Schema::hasColumn('staff_members', 'normalized_mobile')) {
                $table->string('normalized_mobile')->nullable()->index()->after('mobile');
            }
            if (! Schema::hasColumn('staff_members', 'staff_status')) {
                $table->string('staff_status')->default('active')->index()->after('normalized_mobile');
            }
            if (! Schema::hasColumn('staff_members', 'portal_access_enabled')) {
                $table->boolean('portal_access_enabled')->default(true)->after('staff_status');
            }
            if (! Schema::hasColumn('staff_members', 'address')) {
                $table->string('address')->nullable()->after('portal_access_enabled');
                $table->string('emergency_contact')->nullable()->after('address');
                $table->string('payment_frequency')->nullable()->after('emergency_contact');
            }
            if (! Schema::hasColumn('staff_members', 'servicem8_invite_status')) {
                $table->string('servicem8_invite_status')->default('not_invited')->index()->after('servicem8_sync_message');
                $table->timestamp('servicem8_invited_at')->nullable()->after('servicem8_invite_status');
                $table->foreignId('servicem8_invited_by')->nullable()->after('servicem8_invited_at')->constrained('users')->nullOnDelete();
                $table->text('servicem8_status_note')->nullable()->after('servicem8_invited_by');
                $table->timestamp('archived_at')->nullable()->after('servicem8_status_note');
            }
        });

        Schema::table('subcontractor_onboardings', function (Blueprint $table): void {
            if (! Schema::hasColumn('subcontractor_onboardings', 'normalized_email')) {
                $table->string('normalized_email')->nullable()->index()->after('email');
            }
            if (! Schema::hasColumn('subcontractor_onboardings', 'normalized_mobile')) {
                $table->string('normalized_mobile')->nullable()->index()->after('mobile');
            }
            if (! Schema::hasColumn('subcontractor_onboardings', 'staff_member_id')) {
                $table->foreignId('staff_member_id')->nullable()->after('portal_user_id')->constrained('staff_members')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('staff_identity_locks')) {
            Schema::create('staff_identity_locks', function (Blueprint $table): void {
                $table->id();
                $table->string('normalized_email')->nullable()->unique();
                $table->string('normalized_mobile')->nullable()->unique();
                $table->string('owner_type');
                $table->unsignedBigInteger('owner_id');
                $table->timestamps();
                $table->index(['owner_type', 'owner_id']);
            });
        }

        if (! Schema::hasTable('staff_otps')) {
            Schema::create('staff_otps', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
                $table->string('normalized_mobile')->index();
                $table->string('otp_hash');
                $table->timestamp('expires_at')->index();
                $table->timestamp('consumed_at')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->string('request_ip')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('staff_invoice_submissions')) {
            Schema::create('staff_invoice_submissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
                $table->date('invoice_period')->index();
                $table->decimal('total_amount', 12, 2);
                $table->string('invoice_reference')->nullable();
                $table->string('original_filename');
                $table->string('storage_path');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('checksum', 64)->nullable();
                $table->string('status')->default('submitted')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->string('submitted_ip')->nullable();
                $table->timestamps();
                $table->unique(['staff_member_id', 'invoice_period']);
            });
        }

        if (! Schema::hasTable('monthly_xero_reports')) {
            Schema::create('monthly_xero_reports', function (Blueprint $table): void {
                $table->id();
                $table->date('report_month')->index();
                $table->string('report_name');
                $table->string('report_type')->nullable();
                $table->string('original_filename');
                $table->string('storage_path');
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoice_ai_reviews')) {
            Schema::create('invoice_ai_reviews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_invoice_submission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('monthly_xero_report_id')->nullable()->constrained()->nullOnDelete();
                $table->string('provider')->default('gemini');
                $table->string('status')->default('pending')->index();
                $table->decimal('confidence', 5, 2)->nullable();
                $table->text('summary')->nullable();
                $table->json('response')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('staff_profile_change_requests')) {
            Schema::create('staff_profile_change_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('staff_member_id')->constrained()->cascadeOnDelete();
                $table->json('changes');
                $table->string('status')->default('pending')->index();
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_note')->nullable();
                $table->timestamps();
            });
        }

        $normaliseEmail = fn (?string $email): ?string => filled($email) ? mb_strtolower(trim($email)) : null;
        $normaliseMobile = function (?string $mobile): ?string {
            $number = preg_replace('/[^0-9+]/', '', (string) $mobile);
            if ($number === '') return null;
            if (str_starts_with($number, '+61')) return '+61'.preg_replace('/[^0-9]/', '', substr($number, 3));
            if (str_starts_with($number, '61')) return '+'.$number;
            if (str_starts_with($number, '04')) return '+61'.substr($number, 1);
            if (str_starts_with($number, '4') && strlen($number) === 9) return '+61'.$number;
            return $number;
        };

        $seenEmails = [];
        $seenMobiles = [];
        DB::table('staff_members')->orderBy('id')->each(function (object $staff) use ($normaliseEmail, $normaliseMobile, &$seenEmails, &$seenMobiles): void {
            $email = $normaliseEmail($staff->email ?? null);
            $mobile = $normaliseMobile($staff->mobile ?? null);
            $safeEmail = $email && ! isset($seenEmails[$email]) ? $email : null;
            $safeMobile = $mobile && ! isset($seenMobiles[$mobile]) ? $mobile : null;
            if ($safeEmail) $seenEmails[$safeEmail] = true;
            if ($safeMobile) $seenMobiles[$safeMobile] = true;

            DB::table('staff_members')->where('id', $staff->id)->update(['public_id' => (string) Str::uuid()]);
            DB::table('staff_members')->where('id', $staff->id)->update([
                'normalized_email' => $safeEmail,
                'normalized_mobile' => $safeMobile,
                'staff_status' => ($staff->active ?? true) ? 'active' : 'inactive',
            ]);
        });

        DB::table('subcontractor_onboardings')->orderBy('id')->each(function (object $onboarding) use ($normaliseEmail, $normaliseMobile): void {
            DB::table('subcontractor_onboardings')->where('id', $onboarding->id)->update([
                'normalized_email' => $normaliseEmail($onboarding->email ?? null),
                'normalized_mobile' => $normaliseMobile($onboarding->mobile ?? $onboarding->phone ?? null),
            ]);
        });

        DB::table('staff_members')->orderBy('id')->each(function (object $staff) use ($normaliseEmail, $normaliseMobile): void {
            DB::table('staff_identity_locks')->insertOrIgnore([
                'normalized_email' => $normaliseEmail($staff->email ?? null),
                'normalized_mobile' => $normaliseMobile($staff->mobile ?? null),
                'owner_type' => \App\Models\StaffMember::class,
                'owner_id' => $staff->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        DB::table('subcontractor_onboardings')->whereNull('staff_member_id')->orderBy('id')->each(function (object $onboarding) use ($normaliseEmail, $normaliseMobile): void {
            DB::table('staff_identity_locks')->insertOrIgnore([
                'normalized_email' => $normaliseEmail($onboarding->email ?? null),
                'normalized_mobile' => $normaliseMobile($onboarding->mobile ?? $onboarding->phone ?? null),
                'owner_type' => \App\Models\SubcontractorOnboarding::class,
                'owner_id' => $onboarding->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profile_change_requests');
        Schema::dropIfExists('invoice_ai_reviews');
        Schema::dropIfExists('monthly_xero_reports');
        Schema::dropIfExists('staff_invoice_submissions');
        Schema::dropIfExists('staff_otps');
        Schema::dropIfExists('staff_identity_locks');
    }
};
