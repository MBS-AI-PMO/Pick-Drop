<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $isReceipt ? 'Receipt' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
</head>
<body style="margin:0;padding:0;width:100%;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111111;line-height:1.5;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#ffffff;">
        <tr>
            <td align="center" style="padding:16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;">
                    <tr>
                        <td style="padding:0;word-break:break-word;overflow-wrap:anywhere;">
                            <p style="margin:0 0 16px;font-size:16px;">Hi {{ $invoice->customer?->name ?? 'there' }},</p>
                            @if($isReceipt)
                                <p style="margin:0 0 20px;font-size:15px;">We received your payment to our bank account. Your invoice PDF and receipt PDF are attached.</p>
                            @elseif(!empty($pendingBank))
                                <p style="margin:0 0 20px;font-size:15px;">We received your bank transfer to {{ $settings->bank_name ?: 'our PickDrop account' }}. Your invoice PDF is attached. We will confirm the payment once the amount is verified.</p>
                            @else
                                <p style="margin:0 0 20px;font-size:15px;">Your invoice PDF is attached. Pay by bank transfer using the account below. Payment is due by {{ $invoice->due_date?->format('F j, Y') }}.</p>
                            @endif

                            <p style="margin:0;font-size:26px;font-weight:bold;line-height:1.2;">{{ $isReceipt ? 'Receipt' : 'Invoice' }}</p>
                            <p style="margin:6px 0 0;font-size:16px;font-weight:bold;">PickDrop</p>

                            <p style="margin:14px 0 0;font-size:14px;line-height:1.7;">
                                <span style="color:#6b7280;">Invoice number</span><br>
                                <strong>{{ $invoice->invoice_number }}</strong><br>
                                @if($isReceipt)
                                    <span style="color:#6b7280;">Date paid</span><br>
                                    <strong>{{ optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y') }}</strong>
                                @else
                                    <span style="color:#6b7280;">Date of issue</span><br>
                                    <strong>{{ optional($invoice->issue_date)->format('F j, Y') }}</strong><br>
                                    <span style="color:#6b7280;">Date due</span><br>
                                    <strong>{{ optional($invoice->due_date)->format('F j, Y') }}</strong>
                                @endif
                            </p>

                            <p style="margin:18px 0 0;font-size:14px;line-height:1.7;">
                                <strong>{{ $settings->company_name ?: 'PickDrop' }}</strong><br>
                                @if($settings->company_address){{ $settings->company_address }}<br>@endif
                                {{ $settings->company_email }}
                                @if($settings->company_phone)<br>{{ $settings->company_phone }}@endif
                            </p>

                            <p style="margin:14px 0 0;font-size:14px;line-height:1.7;">
                                <strong>Bill to</strong><br>
                                {{ $invoice->customer?->name ?: 'Customer' }}<br>
                                {{ $invoice->customer?->email }}
                                @if($invoice->student)<br><span style="color:#6b7280;">Student: {{ $invoice->student->name }}</span>@endif
                            </p>

                            <p style="font-size:16px;font-weight:bold;margin:20px 0 14px;">
                                @if($isReceipt)
                                    {{ $invoice->formatMoney((float) $invoice->amount_paid) }} paid on {{ optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y') }}
                                @else
                                    {{ $invoice->formatMoney($invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->total) }} due {{ optional($invoice->due_date)->format('F j, Y') }}
                                @endif
                            </p>

                            @foreach($invoice->items as $item)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-bottom:1px solid #ececec;">
                                    <tr>
                                        <td style="padding:12px 0 4px;font-size:14px;word-break:break-word;overflow-wrap:anywhere;">{{ $item->description }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 12px;font-size:13px;color:#374151;word-break:break-word;">
                                            Qty {{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}
                                            · {{ $invoice->formatMoney((float) $item->unit_price) }}
                                            · <strong style="color:#111111;">{{ $invoice->formatMoney((float) $item->total) }}</strong>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:12px;font-size:14px;">
                                <tr>
                                    <td style="padding:6px 8px 6px 0;">Subtotal</td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;">{{ $invoice->formatMoney((float) $invoice->subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 8px 6px 0;">Tax</td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;">{{ $invoice->formatMoney((float) $invoice->tax_amount) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 8px 6px 0;"><strong>Total</strong></td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;"><strong>{{ $invoice->formattedTotal() }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 8px 6px 0;"><strong>{{ $isReceipt ? 'Amount paid' : 'Amount due' }}</strong></td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;"><strong>{{ $isReceipt ? $invoice->formatMoney((float) $invoice->amount_paid) : $invoice->formatMoney($invoice->balance()) }}</strong></td>
                                </tr>
                            </table>

                            @if(!$isReceipt && $settings->hasBankDetails())
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0 0;font-size:14px;background:#f9fafb;border:1px solid #ececec;">
                                    <tr>
                                        <td style="padding:14px;word-break:break-word;overflow-wrap:anywhere;line-height:1.7;">
                                            <strong>Pay to this bank account</strong><br>
                                            {{ $settings->bank_name }}<br>
                                            {{ $settings->bank_account_title }}<br>
                                            A/C {{ $settings->bank_account_number }}
                                            @if($settings->bank_iban)<br>IBAN {{ $settings->bank_iban }}@endif
                                            <br>Use <strong>{{ $invoice->invoice_number }}</strong> as the payment reference.
                                        </td>
                                    </tr>
                                </table>
                            @endif

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
