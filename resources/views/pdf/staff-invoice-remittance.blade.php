<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 38px 56px; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, Arial, sans-serif; color: #102033; font-size: 9px; line-height: 1.45; }
        .statement-page { page-break-after: auto; }
        .letterhead { height: 61px; border-bottom: 2px solid #0082c9; }
        .letterhead-table { width: 100%; border-collapse: collapse; }
        .letterhead-table td { vertical-align: middle; }
        .brand-cell { width: 58%; }
        .title-cell { width: 42%; text-align: right; }
        .logo { width: 39px; height: 39px; object-fit: contain; vertical-align: middle; }
        .brand-copy { display: inline-block; margin-left: 9px; vertical-align: middle; }
        .company-name { font-size: 13px; font-weight: bold; color: #0f172a; }
        .company-abn { margin-top: 2px; font-size: 7.5px; color: #64748b; }
        .document-title { font-size: 17px; font-weight: bold; color: #0f172a; }
        .paid-badge { display: inline-block; margin-top: 4px; padding: 2px 8px; border: 1px solid #86efac; border-radius: 10px; color: #047857; background: #f0fdf4; font-size: 7px; font-weight: bold; letter-spacing: .09em; text-transform: uppercase; }
        .company-line { margin: 8px 0 11px; color: #64748b; font-size: 7.5px; white-space: nowrap; }
        .summary { width: 100%; border-collapse: collapse; border-top: 1px solid #d9e3ea; border-bottom: 1px solid #d9e3ea; background: #f8fafc; }
        .summary td { padding: 10px 9px; vertical-align: top; }
        .summary-detail { width: 17%; }
        .summary-recipient { width: 32%; }
        .summary-total { width: 17%; text-align: right; background: #edf8fb; }
        .eyebrow { color: #718096; font-size: 6.8px; font-weight: bold; letter-spacing: .1em; text-transform: uppercase; }
        .summary-value { margin-top: 3px; color: #102033; font-size: 9px; font-weight: bold; }
        .recipient-name { margin-top: 3px; color: #102033; font-size: 11px; font-weight: bold; }
        .recipient-meta { margin-top: 2px; color: #64748b; font-size: 7.4px; }
        .total-value { margin-top: 2px; color: #0082c9; font-size: 17px; font-weight: bold; white-space: nowrap; }
        .currency { margin-top: 1px; color: #64748b; font-size: 7px; font-weight: bold; letter-spacing: .08em; }
        .statement-heading { margin: 15px 0 7px; }
        .statement-heading h2 { margin: 0; font-size: 11px; color: #0f172a; }
        .statement-heading p { margin: 2px 0 0; color: #64748b; font-size: 7.5px; }
        .continuation { margin: 9px 0 10px; color: #64748b; font-size: 7.5px; }
        .reconciliation { width: 100%; border-collapse: collapse; border: 1px solid #d9e3ea; }
        .reconciliation thead { display: table-header-group; }
        .reconciliation tr { page-break-inside: avoid; }
        .reconciliation th { padding: 6px 9px; background: #eef3f7; border-bottom: 1px solid #d9e3ea; color: #64748b; font-size: 6.8px; font-weight: bold; letter-spacing: .1em; text-align: left; text-transform: uppercase; }
        .reconciliation th.amount, .reconciliation td.amount { text-align: right; }
        .reconciliation td { padding: 6px 9px; border-bottom: 1px solid #e6edf2; font-size: 8px; }
        .reconciliation tr.data-row:nth-child(even) td { background: #fbfdfe; }
        .reconciliation td.code { color: #102033; font-weight: bold; letter-spacing: .02em; }
        .category-row td { padding: 5px 9px; background: #f5f8fa; color: #0082c9; font-size: 6.8px; font-weight: bold; letter-spacing: .11em; text-transform: uppercase; }
        .closing { page-break-inside: avoid; }
        .total-row td { padding: 9px; border-top: 2px solid #0082c9; border-bottom: 0; background: #f3fafc; font-size: 11px; font-weight: bold; }
        .total-row td.amount { color: #0082c9; font-size: 15px; }
        .confirmation { margin-top: 10px; padding: 7px 9px; border-left: 3px solid #0082c9; color: #475569; background: #f8fafc; font-size: 7.3px; line-height: 1.45; }
        .footer { position: fixed; bottom: -39px; left: 0; right: 0; height: 29px; border-top: 1px solid #d9e3ea; padding-top: 6px; color: #64748b; font-size: 6.8px; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: top; }
        .footer-right { text-align: right; }
        .footer-strong { color: #334155; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $companyAddress = collect([
            $business['address_line_1'] ?? null,
            $business['address_line_2'] ?? null,
            $business['address_line_3'] ?? null,
            trim(($business['city'] ?? '').' '.($business['state'] ?? '').' '.($business['postcode'] ?? '')),
            $business['country'] ?? null,
        ])->filter()->implode(', ');
        $contractorName = $staff->legal_business_name ?: $staff->trading_name ?: $staff->fullName();
        $contractorMeta = collect([
            $staff->abn ? 'ABN '.$staff->abn : null,
            $staff->email,
        ])->filter()->implode('  |  ');
        $separateToolsAndMaterials = (float) $breakdown->approved_total > 5000;
        $allocationRows = collect([
            ['label' => 'Labour', 'amount' => $breakdown->labour_amount],
            ['label' => 'Fuel', 'amount' => $breakdown->fuel_amount],
        ]);
        if ($separateToolsAndMaterials) {
            $allocationRows->push([
                'label' => 'Tools',
                'amount' => $breakdown->tools_amount,
            ]);
            $allocationRows->push([
                'label' => 'Materials',
                'amount' => $breakdown->materials_amount,
            ]);
        } else {
            $allocationRows->push([
                'label' => 'Tools & Materials',
                'amount' => $breakdown->tools_materials_amount,
            ]);
        }
        $statementPages = collect();
        $remainingLines = $siteAmounts->values();
        $statementPages->push($remainingLines->take(17)->values());
        $remainingLines = $remainingLines->slice(17)->values();
        foreach ($remainingLines->chunk(22) as $chunk) {
            $statementPages->push($chunk->values());
        }
    @endphp

    <footer class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    <span class="footer-strong">Accounts Department</span> &nbsp; {{ $business['billing_email'] }}
                    @if (! empty($business['phone'])) &nbsp; | &nbsp; {{ $business['phone'] }} @endif
                </td>
                <td class="footer-right">
                    <span class="footer-strong">System Generated Document - No Signature Required</span>
                </td>
            </tr>
        </table>
    </footer>

    @foreach ($statementPages as $pageIndex => $pageLines)
        <section class="statement-page" @if ($pageIndex > 0) style="page-break-before: always;" @endif>
            <header class="letterhead">
                <table class="letterhead-table">
                    <tr>
                        <td class="brand-cell">
                            @if ($logoDataUri)
                                <img class="logo" src="{{ $logoDataUri }}" alt="Hydrox Facility Management">
                            @endif
                            <div class="brand-copy">
                                <div class="company-name">{{ $business['company_name'] }}</div>
                                <div class="company-abn">ABN {{ $business['abn'] }}</div>
                            </div>
                        </td>
                        <td class="title-cell">
                            <div class="document-title">Payment Remittance Advice</div>
                            <span class="paid-badge">Paid</span>
                        </td>
                    </tr>
                </table>
            </header>

            @if ($pageIndex === 0)
                <div class="company-line">
                    {{ $companyAddress }} &nbsp; | &nbsp; {{ $business['billing_email'] }} &nbsp; | &nbsp; {{ $business['website'] }}
                    @if (! empty($business['phone'])) &nbsp; | &nbsp; {{ $business['phone'] }} @endif
                </div>

                <table class="summary">
                    <tr>
                        <td class="summary-recipient">
                            <div class="eyebrow">Paid To</div>
                            <div class="recipient-name">{{ $contractorName }}</div>
                            @if ($contractorMeta)<div class="recipient-meta">{{ $contractorMeta }}</div>@endif
                        </td>
                        <td class="summary-detail">
                            <div class="eyebrow">Payment Month</div>
                            <div class="summary-value">{{ $invoice->invoice_period->format('F Y') }}</div>
                            <div class="eyebrow" style="margin-top:7px;">Currency</div>
                            <div class="summary-value">AUD</div>
                        </td>
                        <td class="summary-detail">
                            <div class="eyebrow">Payment Date</div>
                            <div class="summary-value">{{ $invoice->paid_at?->format('d M Y') }}</div>
                            <div class="eyebrow" style="margin-top:7px;">Payment Method</div>
                            <div class="summary-value">Bank Transfer (EFT)</div>
                        </td>
                        <td class="summary-total">
                            <div class="eyebrow">Total Paid</div>
                            <div class="total-value">${{ number_format((float) $invoice->approved_total, 2) }}</div>
                            <div class="currency">AUD</div>
                        </td>
                    </tr>
                </table>

                <div class="statement-heading">
                    <h2>Work Paid</h2>
                    <p>Approved amounts paid for regular site work and additional work.</p>
                </div>
            @else
                <div class="continuation">
                    Work paid continued &nbsp; | &nbsp; {{ $contractorName }} &nbsp; | &nbsp; {{ $invoice->invoice_period->format('F Y') }}
                </div>
            @endif

            <table class="reconciliation">
                <thead>
                    <tr>
                        <th>Site Code / Work Reference</th>
                        <th class="amount">Amount Paid (AUD)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $previousCategory = null; @endphp
                    @foreach ($pageLines as $line)
                        @if ($line['category'] !== $previousCategory)
                            <tr class="category-row">
                                <td colspan="2">{{ $line['category'] === 'site' ? 'Regular Sites' : 'Additional Work' }}</td>
                            </tr>
                            @php $previousCategory = $line['category']; @endphp
                        @endif
                        <tr class="data-row">
                            <td class="code">{{ $line['code'] }}</td>
                            <td class="amount">${{ number_format((float) $line['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($loop->last)
                <div class="statement-heading closing">
                    <h2>Payment Breakdown</h2>
                    <p>The amounts shown below are a breakdown of the total payment for all jobs listed above. They are not additional charges.</p>
                </div>

                <table class="reconciliation closing">
                    <thead>
                        <tr>
                            <th>Payment Category</th>
                            <th class="amount">Amount (AUD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allocationRows as $line)
                            <tr class="data-row">
                                <td class="code">{{ $line['label'] }}</td>
                                <td class="amount">${{ number_format((float) $line['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td>Total Paid</td>
                            <td class="amount">${{ number_format((float) $invoice->approved_total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="confirmation closing">
                    Payment was completed by Bank Transfer (EFT) to the nominated bank account. This remittance advice is provided for payment reconciliation purposes only and is not a Tax Invoice.
                </div>
            @endif
        </section>
    @endforeach

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
            $pdf->page_text(500, 821, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 6.5, [0.39, 0.45, 0.55]);
        }
    </script>
</body>
</html>
