<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InvoiceSite extends Model
{
    protected $fillable = [
        'site_code',
        'name',
        'active',
        'recurring_pattern',
        'validation_mode',
        'fortnightly_anchor_date',
        'weekly_contract_hours',
        'monday_hours',
        'tuesday_hours',
        'wednesday_hours',
        'thursday_hours',
        'friday_hours',
        'saturday_hours',
        'sunday_hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'fortnightly_anchor_date' => 'date',
            'weekly_contract_hours' => 'decimal:2',
            'monday_hours' => 'decimal:2',
            'tuesday_hours' => 'decimal:2',
            'wednesday_hours' => 'decimal:2',
            'thursday_hours' => 'decimal:2',
            'friday_hours' => 'decimal:2',
            'saturday_hours' => 'decimal:2',
            'sunday_hours' => 'decimal:2',
        ];
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(StaffInvoiceWorkLog::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(InvoiceSiteShift::class);
    }

    public function assignments()
    {
        return $this->hasManyThrough(
            SiteShiftAssignment::class,
            InvoiceSiteShift::class,
            'invoice_site_id',
            'invoice_site_shift_id'
        );
    }

    public function displayName(): string
    {
        return trim(($this->site_code ? $this->site_code.' - ' : '').$this->name);
    }

    public function patternLabel(): string
    {
        return match ($this->recurring_pattern) {
            'fortnightly' => 'Fortnightly',
            'manual' => 'Manual',
            default => 'Weekly',
        };
    }

    public function needsAnchorDate(): bool
    {
        return $this->recurring_pattern === 'fortnightly'
            && blank($this->fortnightly_anchor_date);
    }

    public function canAutoValidate(): bool
    {
        if ($this->validation_mode !== 'auto') {
            return false;
        }

        return ! $this->needsAnchorDate();
    }

    public function expectedHoursForMonth(CarbonInterface $month): float
    {
        if ($this->recurring_pattern === 'fortnightly' && $this->fortnightly_anchor_date) {
            return $this->hoursForMonth($month, 'hours');
        }

        if ($this->recurring_pattern === 'manual') {
            return 0.0;
        }

        return $this->hoursForMonth($month, 'hours');
    }

    public function contractHoursForMonth(CarbonInterface $month): float
    {
        if ($this->recurring_pattern === 'manual') {
            return 0.0;
        }

        if ($this->recurring_pattern === 'fortnightly' && $this->fortnightly_anchor_date) {
            $cursor = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $occurrences = 0;

            while ($cursor->lte($end)) {
                if ($this->isFortnightlyOccurrence($cursor)) {
                    $occurrences++;
                }

                $cursor->addDay();
            }

            return round($occurrences * (float) $this->weekly_contract_hours, 2);
        }

        return round(((float) $this->weekly_contract_hours / 7) * $month->copy()->daysInMonth, 2);
    }

    public function expectedHoursForDate(CarbonInterface $date): float
    {
        return $this->hoursForDate($date, 'hours');
    }

    public function contractHoursForDate(CarbonInterface $date): float
    {
        return round((float) $this->weekly_contract_hours / 7, 2);
    }

    public function freeHoursForMonth(CarbonInterface $month): float
    {
        return round(max(0, $this->contractHoursForMonth($month) - $this->expectedHoursForMonth($month)), 2);
    }

    public function staffHoursForWeek(): float
    {
        return round((float) $this->shifts()
            ->where('active', true)
            ->get()
            ->sum(fn (InvoiceSiteShift $shift): float => (float) $shift->hours), 2);
    }

    public function freeHoursForWeek(): float
    {
        return round(max(0, (float) $this->weekly_contract_hours - $this->staffHoursForWeek()), 2);
    }

    private function hoursForDate(CarbonInterface $date, string $column): float
    {
        $weekday = strtolower($date->englishDayOfWeek);
        $shiftTotal = $this->shifts()
            ->where('active', true)
            ->where('weekday', $weekday)
            ->get()
            ->sum(fn (InvoiceSiteShift $shift): float => (float) $shift->hours);

        return round((float) $shiftTotal, 2);
    }

    public function hasRosterHoursForDate(CarbonInterface $date): bool
    {
        $dayHours = $this->expectedHoursForDate($date);

        if ($dayHours <= 0) {
            return false;
        }

        if ($this->recurring_pattern !== 'fortnightly' || ! $this->fortnightly_anchor_date) {
            return true;
        }

        return $this->isFortnightlyOccurrence($date);
    }

    public static function nextSiteCode(): string
    {
        $lastNumber = static::query()
            ->whereNotNull('site_code')
            ->pluck('site_code')
            ->map(fn (string $code): int => (int) Str::of($code)->match('/HYD(\d+)/i')->toString())
            ->max() ?: 0;

        return 'HYD'.str_pad((string) ($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    }

    private function hoursForMonth(CarbonInterface $month, string $column): float
    {
        $cursor = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $total = 0.0;

        while ($cursor->lte($end)) {
            if ($this->recurring_pattern === 'fortnightly') {
                if ($this->hasRosterHoursForDate($cursor)) {
                    $total += $this->hoursForDate($cursor, $column);
                }
            } else {
                $total += $this->hoursForDate($cursor, $column);
            }

            $cursor->addDay();
        }

        return round($total, 2);
    }

    private function isFortnightlyOccurrence(CarbonInterface $date): bool
    {
        if (! $this->fortnightly_anchor_date) {
            return false;
        }

        $days = $this->fortnightly_anchor_date
            ->copy()
            ->startOfDay()
            ->diffInDays($date->copy()->startOfDay(), false);

        return $days % 14 === 0;
    }
}
