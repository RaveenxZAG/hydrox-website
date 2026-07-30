<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->json('services')->nullable()->after('service');
            $table->json('extras')->nullable()->after('services');
            $table->string('frequency', 40)->nullable()->after('extras');
            $table->boolean('schedule_flexible')->default(true)->after('frequency');
            $table->timestamp('finalized_at')->nullable()->after('payload');
            $table->timestamp('customer_email_sent_at')->nullable()->after('finalized_at');
            $table->timestamp('admin_email_sent_at')->nullable()->after('customer_email_sent_at');
            $table->text('email_error')->nullable()->after('admin_email_sent_at');
        });

        Schema::create('booking_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_photos');

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'services',
                'extras',
                'frequency',
                'schedule_flexible',
                'finalized_at',
                'customer_email_sent_at',
                'admin_email_sent_at',
                'email_error',
            ]);
        });
    }
};
