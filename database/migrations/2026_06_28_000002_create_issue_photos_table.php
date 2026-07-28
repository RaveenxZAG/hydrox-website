<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('issue_photos')) {
            Schema::create('issue_photos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('issue_found_id')->constrained('issue_founds')->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->timestamp('captured_at')->nullable();
                $table->timestamps();
            });
        }

        DB::table('issue_founds')
            ->whereNotNull('photo_path')
            ->orderBy('id')
            ->get(['id', 'photo_path', 'created_at', 'updated_at'])
            ->each(function ($issue): void {
                DB::table('issue_photos')->insert([
                    'issue_found_id' => $issue->id,
                    'path' => $issue->photo_path,
                    'original_name' => basename($issue->photo_path),
                    'captured_at' => $issue->created_at,
                    'created_at' => $issue->created_at,
                    'updated_at' => $issue->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_photos');
    }
};
