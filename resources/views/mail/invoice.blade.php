<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $isReceipt ? 'Receipt' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#111111;line-height:1.5;margin:0;padding:24px;background:#ffffff;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;margin:0 auto;background:#ffffff;">
        <tr>
            <td style="position:relative;">
                <div style="position:absolute;top:240px;left:0;width:100%;text-align:center;font-size:72px;line-height:1;font-weight:700;color:#ececec;letter-spacing:8px;transform:rotate(-30deg);-webkit-transform:rotate(-30deg);white-space:nowrap;z-index:0;pointer-events:none;">PickDrop</div>
                <div style="position:relative;z-index:1;">
                <p style="margin:0 0 16px;">Hi {{ $invoice->customer?->name ?? 'there' }},</p>
                @if($isReceipt)
                    <p style="margin:0 0 20px;">We received your payment to our bank account. Your invoice PDF and receipt PDF are attached.</p>
                @elseif(!empty($pendingBank))
                    <p style="margin:0 0 20px;">We received your bank transfer to {{ $settings->bank_name ?: 'our PickDrop account' }}. Your invoice PDF is attached. We will confirm the payment once the amount is verified.</p>
                @else
                    <p style="margin:0 0 20px;">Your invoice PDF is attached. Pay by bank transfer using the account below. Payment is due by {{ $invoice->due_date?->format('F j, Y') }}.</p>
                @endif

                <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                    <tr>
                        <td valign="top">
                            <div style="font-size:28px;font-weight:bold;">{{ $isReceipt ? 'Receipt' : 'Invoice' }}</div>
                            <p style="margin:10px 0 0;font-size:14px;line-height:1.7;">
                                <span style="color:#6b7280;">Invoice number</span> &nbsp; <strong>{{ $invoice->invoice_number }}</strong><br>
                                @if($isReceipt)
                                    <span style="color:#6b7280;">Date paid</span> &nbsp; <strong>{{ optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y') }}</strong>
                                @else
                                    <span style="color:#6b7280;">Date of issue</span> &nbsp; <strong>{{ optional($invoice->issue_date)->format('F j, Y') }}</strong><br>
                                    <span style="color:#6b7280;">Date due</span> &nbsp; <strong>{{ optional($invoice->due_date)->format('F j, Y') }}</strong>
                                @endif
                            </p>
                        </td>
                        <td valign="top" align="right" style="font-size:16px;font-weight:bold;">PickDrop</td>
                    </tr>
                </table>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;font-size:14px;">
                    <tr>
                        <td width="50%" valign="top">
                            <strong>{{ $settings->company_name ?: 'PickDrop' }}</strong><br>
                            @if($settings->company_address){{ $settings->company_address }}<br>@endif
                            {{ $settings->company_email }}
                            @if($settings->company_phone)<br>{{ $settings->company_phone }}@endif
                        </td>
                        <td width="50%" valign="top">
                            <strong>Bill to</strong><br>
                            {{ $invoice->customer?->name ?: 'Customer' }}<br>
                            {{ $invoice->customer?->email }}
                            @if($invoice->student)<br><span style="color:#6b7280;">Student: {{ $invoice->student->name }}</span>@endif
                        </td>
                    </tr>
                </table>

                <p style="font-size:16px;font-weight:bold;margin:0 0 18px;">
                    @if($isReceipt)
                        {{ $invoice->formatMoney((float) $invoice->amount_paid) }} paid on {{ optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y') }}
                    @else
                        {{ $invoice->formatMoney($invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->total) }} due {{ optional($invoice->due_date)->format('F j, Y') }}
                    @endif
                </p>

                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
                    <tr>
                        <th align="left" style="border-bottom:1px solid #111111;color:#6b7280;font-weight:600;">Description</th>
                        <th align="right" style="border-bottom:1px solid #111111;color:#6b7280;font-weight:600;">Qty</th>
                        <th align="right" style="border-bottom:1px solid #111111;color:#6b7280;font-weight:600;">Unit price</th>
                        <th align="right" style="border-bottom:1px solid #111111;color:#6b7280;font-weight:600;">Amount</th>
                    </tr>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td style="border-bottom:1px solid #ececec;">{{ $item->description }}</td>
                            <td align="right" style="border-bottom:1px solid #ececec;">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                            <td align="right" style="border-bottom:1px solid #ececec;">{{ $invoice->formatMoney((float) $item->unit_price) }}</td>
                            <td align="right" style="border-bottom:1px solid #ececec;">{{ $invoice->formatMoney((float) $item->total) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" align="right" style="padding-top:10px;">Subtotal</td>
                        <td align="right" style="padding-top:10px;">{{ $invoice->formatMoney((float) $invoice->subtotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right">Tax</td>
                        <td align="right">{{ $invoice->formatMoney((float) $invoice->tax_amount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right"><strong>Total</strong></td>
                        <td align="right"><strong>{{ $invoice->formattedTotal() }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right"><strong>{{ $isReceipt ? 'Amount paid' : 'Amount due' }}</strong></td>
                        <td align="right"><strong>{{ $isReceipt ? $invoice->formatMoney((float) $invoice->amount_paid) : $invoice->formatMoney($invoice->balance()) }}</strong></td>
                    </tr>
                </table>

                @if(!$isReceipt && $settings->hasBankDetails())
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 0;font-size:13px;background:#f9fafb;border:1px solid #ececec;">
                        <tr>
                            <td style="padding:14px 16px;">
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

                <p style="margin:24px 0 0;font-size:13px;color:#6b7280;">
                    {{ $settings->company_name ?: 'PickDrop' }}
                    @if($settings->company_email) · {{ $settings->company_email }}@endif
                </p>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
