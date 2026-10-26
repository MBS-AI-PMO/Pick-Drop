<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Payment received {{ $invoice->invoice_number }}</title>
</head>
<body style="margin:0;padding:0;width:100%;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111111;line-height:1.5;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#ffffff;">
        <tr>
            <td align="center" style="padding:16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;">
                    <tr>
                        <td style="padding:0;word-break:break-word;overflow-wrap:anywhere;">
                            <p style="margin:0 0 16px;font-size:16px;">Hi {{ $invoice->customer?->name ?? 'there' }},</p>
                            <p style="margin:0 0 20px;font-size:15px;">
                                Thank you. We have received a payment of <strong>{{ $invoice->formatMoney((float) $payment->amount) }}</strong>
                                toward invoice <strong>{{ $invoice->invoice_number }}</strong>.
                                A balance of <strong>{{ $invoice->formatMoney($invoice->balance()) }}</strong> is still remaining.
                                Your invoice PDF is attached.
                            </p>

                            <p style="margin:0;font-size:26px;font-weight:bold;line-height:1.2;">Payment received</p>
                            <p style="margin:6px 0 0;font-size:16px;font-weight:bold;">PickDrop</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:18px;font-size:14px;border-top:1px solid #ececec;">
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Invoice number</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;"><strong>{{ $invoice->invoice_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Invoice total</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;white-space:nowrap;">{{ $invoice->formattedTotal() }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">
                                        Payment received
                                        @if($payment->paid_at) <span style="font-size:12px;">({{ $payment->paid_at->format('F j, Y') }})</span>@endif
                                    </td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;white-space:nowrap;"><strong>{{ $invoice->formatMoney((float) $payment->amount) }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Total paid so far</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;white-space:nowrap;">{{ $invoice->formatMoney((float) $invoice->amount_paid) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 8px 12px 0;border-bottom:1px solid #ececec;"><strong>Remaining balance</strong></td>
                                    <td align="right" style="padding:12px 0;border-bottom:1px solid #ececec;white-space:nowrap;font-size:16px;"><strong>{{ $invoice->formatMoney($invoice->balance()) }}</strong></td>
                                </tr>
                                @if($invoice->due_date)
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Balance due by</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;">{{ $invoice->due_date->format('F j, Y') }}</td>
                                </tr>
                                @endif
                            </table>

                            @if($settings->hasBankDetails())
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0 0;font-size:14px;background:#f9fafb;border:1px solid #ececec;">
                                    <tr>
                                        <td style="padding:14px;word-break:break-word;overflow-wrap:anywhere;line-height:1.7;">
                                            <strong>Pay the remaining balance to this bank account</strong><br>
                                            {{ $settings->bank_name }}<br>
                                            {{ $settings->bank_account_title }}<br>
                                            A/C {{ $settings->bank_account_number }}
                                            @if($settings->bank_iban)<br>IBAN {{ $settings->bank_iban }}@endif
                                            <br>Use <strong>{{ $invoice->invoice_number }}</strong> as the payment reference.
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:18px 0 0;font-size:14px;line-height:1.7;">
                                <strong>Bill to</strong><br>
                                {{ $invoice->customer?->name ?: 'Customer' }}<br>
                                {{ $invoice->customer?->email }}
                                @if($invoice->student)<br><span style="color:#6b7280;">Student: {{ $invoice->student->name }}</span>@endif
                            </p>

                            <p style="margin:20px 0 0;font-size:13px;color:#6b7280;word-break:break-word;overflow-wrap:anywhere;">
                                {{ $settings->company_name ?: 'PickDrop' }}
                                @if($settings->company_email) · {{ $settings->company_email }}@endif
                                @if($settings->company_phone) · {{ $settings->company_phone }}@endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
