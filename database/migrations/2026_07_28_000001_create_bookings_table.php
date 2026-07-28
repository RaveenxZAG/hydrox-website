<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->string('source')->default('hydrox.au');
            $table->string('external_reference')->nullable()->index();
            $table->string('status')->default('new')->index();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('service');
            $table->date('preferred_date')->nullable()->index();
            $table->string('preferred_time', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('suburb', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->text('notes')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
