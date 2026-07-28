<?php

namespace App\Services;

use App\Models\StaffInvoiceRemittanceBreakdown;
use App\Models\StaffInvoiceSubmission;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RemittanceBreakdownService
{
    private const PERCENT_SCALE = 10000;

    public function resolve(StaffInvoiceSubmission $invoice): StaffInvoiceRemittanceBreakdown
    {
        if ($invoice->approved_total === null) {
            throw new RuntimeException('An approved remittance total is required.');
        }

        return DB::transaction(function () use ($invoice): StaffInvoiceRemittanceBreakdown {
            $lockedInvoice = StaffInvoiceSubmission::query()->lockForUpdate()->findOrFail($invoice->id);
            $existing = StaffInvoiceRemittanceBreakdown::query()
                ->where('staff_invoice_submission_id', $lockedInvoice->id)
                ->first();

            if ($existing) {
                $this->validate($existing, $this->toCents((string) $lockedInvoice->approved_total));

                return $existing;
            }

            $breakdown = $this->generate($lockedInvoice);
            $this->validate($breakdown, $this->toCents((string) $lockedInvoice->approved_total));
            $breakdown->save();

            return $breakdown;
        }, 3);
    }

    private function generate(StaffInvoiceSubmission $invoice): StaffInvoiceRemittanceBreakdown
    {
        $totalCents = $this->toCents((string) $invoice->approved_total);
        if ($totalCents <= 0) {
            throw new RuntimeException('The approved remittance total must be greater than zero.');
        }

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $fuelBasisPoints = random_int(200, 700);
            $toolsMaterialsBasisPoints = random_int(500, 1300);

            if ($fuelBasisPoints + $toolsMaterialsBasisPoints > 2000) {
                continue;
            }

            $fuelCents = $this->percentageOf($totalCents, $fuelBasisPoints);
            $toolsMaterialsCents = $this->percentageOf($totalCents, $toolsMaterialsBasisPoints);
            $toolsSplitBasisPoints = null;
            $toolsCents = null;
            $materialsCents = null;

            if ($totalCents > 500000) {
                $toolsSplitBasisPoints = random_int(3500, 5000);
                $toolsCents = $this->percentageOf($toolsMaterialsCents, $toolsSplitBasisPoints);
                $materialsCents = $toolsMaterialsCents - $toolsCents;
            }

            $labourCents = $totalCents - $fuelCents - $toolsMaterialsCents;
            $labourBasisPoints = self::PERCENT_SCALE - $fuelBasisPoints - $toolsMaterialsBasisPoints;

            $breakdown = new StaffInvoiceRemittanceBreakdown([
                'staff_invoice_submission_id' => $invoice->id,
                'staff_member_id' => $invoice->staff_member_id,
                'approved_total' => $this->fromCents($totalCents),
                'fuel_percentage' => $this->fromBasisPoints($fuelBasisPoints),
                'fuel_amount' => $this->fromCents($fuelCents),
                'tools_materials_percentage' => $this->fromBasisPoints($toolsMaterialsBasisPoints),
                'tools_materials_amount' => $this->fromCents($toolsMaterialsCents),
                'tools_split_percentage' => $toolsSplitBasisPoints === null ? null : $this->fromBasisPoints($toolsSplitBasisPoints),
                'tools_amount' => $toolsCents === null ? null : $this->fromCents($toolsCents),
                'materials_split_percentage' => $toolsSplitBasisPoints === null ? null : $this->fromBasisPoints(self::PERCENT_SCALE - $toolsSplitBasisPoints),
                'materials_amount' => $materialsCents === null ? null : $this->fromCents($materialsCents),
                'labour_percentage' => $this->fromBasisPoints($labourBasisPoints),
                'labour_amount' => $this->fromCents($labourCents),
                'generated_at' => now(),
                'generation_status' => 'generated',
            ]);

            try {
                $this->validate($breakdown, $totalCents);

                return $breakdown;
            } catch (RuntimeException) {
                // Reject the invalid random set and try another permitted set.
            }
        }

        throw new RuntimeException('A valid remittance breakdown could not be generated.');
    }

    private function validate(StaffInvoiceRemittanceBreakdown $breakdown, int $approvedTotalCents): void
    {
        $storedTotalCents = $this->toCents((string) $breakdown->approved_total);
        $fuelCents = $this->toCents((string) $breakdown->fuel_amount);
        $toolsMaterialsCents = $this->toCents((string) $breakdown->tools_materials_amount);
        $labourCents = $this->toCents((string) $breakdown->labour_amount);
        $fuelBasisPoints = $this->toBasisPoints((string) $breakdown->fuel_percentage);
        $toolsMaterialsBasisPoints = $this->toBasisPoints((string) $breakdown->tools_materials_percentage);
        $labourBasisPoints = $this->toBasisPoints((string) $breakdown->labour_percentage);

        if ($storedTotalCents !== $approvedTotalCents) {
            throw new RuntimeException('The saved breakdown does not match the approved remittance total.');
        }
        if (min($fuelCents, $toolsMaterialsCents, $labourCents) < 0) {
            throw new RuntimeException('Remittance breakdown amounts cannot be negative.');
        }
        if ($fuelBasisPoints < 200 || $fuelBasisPoints > 700) {
            throw new RuntimeException('Fuel must be between 2.00% and 7.00%.');
        }
        if ($toolsMaterialsBasisPoints < 500 || $toolsMaterialsBasisPoints > 1300) {
            throw new RuntimeException('Tools and materials must be between 5.00% and 13.00%.');
        }
        if ($fuelBasisPoints + $toolsMaterialsBasisPoints > 2000 || $labourBasisPoints < 8000) {
            throw new RuntimeException('Non-labour allocation cannot exceed 20.00%.');
        }
        if ($labourBasisPoints <= max($fuelBasisPoints, $toolsMaterialsBasisPoints)) {
            throw new RuntimeException('Labour percentage must be the largest allocation.');
        }

        if ($approvedTotalCents <= 500000) {
            if ($breakdown->tools_amount !== null || $breakdown->materials_amount !== null) {
                throw new RuntimeException('Tools and materials must remain combined at $5,000.00 or less.');
            }
            if ($labourCents <= max($fuelCents, $toolsMaterialsCents)) {
                throw new RuntimeException('Labour amount must be the largest allocation.');
            }
            $displayedTotal = $labourCents + $fuelCents + $toolsMaterialsCents;
        } else {
            if ($breakdown->tools_amount === null || $breakdown->materials_amount === null || $breakdown->tools_split_percentage === null) {
                throw new RuntimeException('Tools and materials must be separated above $5,000.00.');
            }
            $toolsCents = $this->toCents((string) $breakdown->tools_amount);
            $materialsCents = $this->toCents((string) $breakdown->materials_amount);
            $toolsSplitBasisPoints = $this->toBasisPoints((string) $breakdown->tools_split_percentage);
            if ($toolsSplitBasisPoints < 3500 || $toolsSplitBasisPoints > 5000 || $toolsCents + $materialsCents !== $toolsMaterialsCents) {
                throw new RuntimeException('The tools and materials split is invalid.');
            }
            if ($labourCents <= max($fuelCents, $toolsCents, $materialsCents)) {
                throw new RuntimeException('Labour amount must be the largest allocation.');
            }
            $displayedTotal = $labourCents + $fuelCents + $toolsCents + $materialsCents;
        }

        if ($displayedTotal !== $approvedTotalCents) {
            throw new RuntimeException('Remittance rows must add exactly to the approved total.');
        }
    }

    private function percentageOf(int $cents, int $basisPoints): int
    {
        return intdiv(($cents * $basisPoints) + 5000, self::PERCENT_SCALE);
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new RuntimeException('Currency values must contain no more than two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function fromCents(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function toBasisPoints(string $percentage): int
    {
        return (int) round(((float) $percentage) * 100);
    }

    private function fromBasisPoints(int $basisPoints): string
    {
        return number_format($basisPoints / 100, 4, '.', '');
    }
}
