<?php

namespace App\Services\StaffPortal;

use Carbon\CarbonImmutable;

class InvoicePeriodService
{
    public function currentWindow(): array
    {
        $now = CarbonImmutable::now(config('app.timezone', 'Australia/Darwin'));
        $period = $now->startOfMonth()->subMonth();
        $opens = $now->startOfMonth();
        $closes = $now->startOfMonth()->addDays(6)->endOfDay();
        $localTestingUnlocked = app()->environment('local');

        return [
            'period' => $period,
            'opens_at' => $opens,
            'closes_at' => $closes,
            'is_open' => $localTestingUnlocked || $now->betweenIncluded($opens, $closes),
            'local_testing_unlocked' => $localTestingUnlocked,
            'label' => $period->format('F Y'),
        ];
    }
}
