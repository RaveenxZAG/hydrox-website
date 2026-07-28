<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;background:#f3f7fa;font-family:Arial,Helvetica,sans-serif;color:#132238;">
    <div style="padding:32px 16px;">
        <div style="max-width:640px;margin:0 auto;overflow:hidden;border:1px solid #dce7ee;border-radius:16px;background:#ffffff;box-shadow:0 10px 30px rgba(15,35,55,.06);">
            <div style="border-top:5px solid #0082c9;padding:26px 30px 20px;">
                <div style="font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0082c9;">Hydrox Facility Management</div>
                <h1 style="margin:8px 0 0;font-size:26px;line-height:1.25;color:#0f172a;">Your payment has been processed</h1>
            </div>

            <div style="padding:0 30px 30px;">
                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#475569;">Hi {{ $staff->first_name ?: $staff->fullName() }},</p>
                <p style="margin:0 0 22px;font-size:15px;line-height:1.7;color:#475569;">Your payment for {{ $invoice->invoice_period->format('F Y') }} has been completed by Bank Transfer (EFT). Your official remittance advice is attached to this email.</p>

                <div style="margin:0 0 24px;border:1px solid #dce7ee;border-radius:14px;background:#f8fafc;padding:20px;">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;">Total paid</div>
                    <div style="margin-top:5px;font-size:30px;font-weight:800;color:#0082c9;">${{ number_format((float) $invoice->approved_total, 2) }} AUD</div>
                    <div style="margin-top:10px;font-size:13px;color:#64748b;">Payment date: {{ $invoice->paid_at?->format('d M Y') }}</div>
                </div>

                <p style="margin:0 0 22px;font-size:14px;line-height:1.65;color:#475569;">You can also download the remittance from the Hydrox Facility Management staff portal at any time.</p>
                <a href="{{ route('staff-portal.login', ['action' => 'invoice']) }}" style="display:inline-block;border-radius:10px;background:#0082c9;padding:12px 18px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">Open Staff Portal</a>
            </div>

            <div style="border-top:1px solid #e5edf2;background:#f8fafc;padding:18px 30px;font-size:12px;line-height:1.6;color:#64748b;">
                <strong style="color:#334155;">Accounts Department</strong><br>
                {{ $business['billing_email'] ?? 'admin@hydrox.au' }}
                @if (! empty($business['phone'])) &nbsp; | &nbsp; {{ $business['phone'] }} @endif
            </div>
        </div>
    </div>
</body>
</html>
