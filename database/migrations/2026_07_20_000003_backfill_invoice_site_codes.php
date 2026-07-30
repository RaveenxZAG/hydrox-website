<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $used = DB::table('invoice_sites')
            ->whereNotNull('site_code')
            ->pluck('site_code')
            ->map(fn (string $code): int => (int) preg_replace('/\D+/', '', $code))
            ->filter()
            ->values();

        $next = max($used->max() ?: 0, 0) + 1;

        DB::table('invoice_sites')
            ->whereNull('site_code')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($site) use (&$next): void {
                DB::table('invoice_sites')
                    ->where('id', $site->id)
                    ->update(['site_code' => 'HYD'.str_pad((string) $next, 3, '0', STR_PAD_LEFT)]);

                $next++;
            });
    }

    public function down(): void
    {
        //
    }
};
