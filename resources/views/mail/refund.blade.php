<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Refund {{ $invoice->invoice_number }}</title>
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
                                We have refunded a payment you made for invoice <strong>{{ $invoice->invoice_number }}</strong>.
                                Here are the details of your refund.
                            </p>

                            <p style="margin:0;font-size:26px;font-weight:bold;line-height:1.2;">Refund</p>
                            <p style="margin:6px 0 0;font-size:16px;font-weight:bold;">PickDrop</p>

                            <p style="font-size:20px;font-weight:bold;margin:20px 0 14px;">
                                {{ $invoice->formatMoney((float) $payment->amount) }} refunded
                                @if($payment->refunded_at) on {{ $payment->refunded_at->format('F j, Y') }}@endif
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;font-size:14px;border-top:1px solid #ececec;">
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Invoice number</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;"><strong>{{ $invoice->invoice_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Refund amount</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;white-space:nowrap;"><strong>{{ $invoice->formatMoney((float) $payment->amount) }}</strong></td>
                                </tr>
                                @if($payment->refunded_at)
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Refund date</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;">{{ $payment->refunded_at->format('F j, Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Original payment</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;">
                                        {{ ucfirst(str_replace('_', ' ', (string) $payment->method)) }}
                                        @if($payment->paid_at) · {{ $payment->paid_at->format('F j, Y') }}@endif
                                    </td>
                                </tr>
                                @if($payment->reference)
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Payment reference</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;">{{ $payment->reference }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Invoice status</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;">
                                        @if($invoice->status === \App\Models\Invoice::STATUS_REFUNDED)
                                            <strong>Refunded</strong>
                                        @else
                                            {{ ucfirst($invoice->status) }}
                                        @endif
                                    </td>
                                </tr>
                                @if($invoice->status !== \App\Models\Invoice::STATUS_REFUNDED)
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Still paid</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;white-space:nowrap;">{{ $invoice->formatMoney((float) $invoice->amount_paid) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 8px 10px 0;border-bottom:1px solid #ececec;color:#6b7280;">Balance due</td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #ececec;white-space:nowrap;"><strong>{{ $invoice->formatMoney($invoice->balance()) }}</strong></td>
                                </tr>
                                @endif
                            </table>

                            <p style="margin:18px 0 0;font-size:14px;line-height:1.7;">
                                <strong>Bill to</strong><br>
                                {{ $invoice->customer?->name ?: 'Customer' }}<br>
                                {{ $invoice->customer?->email }}
                                @if($invoice->student)<br><span style="color:#6b7280;">Student: {{ $invoice->student->name }}</span>@endif
                            </p>

                            <p style="margin:18px 0 0;font-size:14px;line-height:1.7;">
                                If you have any questions about this refund, please contact us
                                @if($settings->company_email) at <strong>{{ $settings->company_email }}</strong>@endif
                                @if($settings->company_phone) or {{ $settings->company_phone }}@endif.
                            </p>

                            <p style="margin:20px 0 0;font-size:13px;color:#6b7280;word-break:break-word;overflow-wrap:anywhere;">
                                {{ $settings->company_name ?: 'PickDrop' }}
                                @if($settings->company_email) · {{ $settings->company_email }}@endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
