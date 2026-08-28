<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $brand }} {{ $isReceipt ? 'Receipt' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 36px 40px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #111111; margin: 0; }
        .title { font-size: 26px; font-weight: bold; }
        .logo { font-size: 16px; font-weight: bold; text-align: right; }
        .muted { color: #6b7280; }
        .label { color: #6b7280; }
        .headline { font-size: 16px; font-weight: bold; margin: 22px 0 18px; }
        table { width: 100%; border-collapse: collapse; }
        .items th { font-size: 10px; color: #6b7280; text-align: left; padding: 0 0 6px; border-bottom: 1px solid #111111; }
        .items td { padding: 8px 0; border-bottom: 1px solid #ececec; }
        .right { text-align: right; }
        .totals { width: 240px; margin-left: auto; margin-top: 8px; }
        .totals td { padding: 6px 0; border-bottom: 1px solid #ececec; }
        .strong td { border-bottom: 0; font-weight: bold; padding-top: 10px; }
        h2 { font-size: 13px; margin: 24px 0 8px; }
        .note { margin-top: 16px; font-size: 10px; color: #6b7280; line-height: 1.5; }
        .watermark {
            position: fixed;
            top: 42%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 78px;
            font-weight: bold;
            color: #efefef;
            letter-spacing: 6px;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="watermark">PickDrop</div>
    @php
        $headline = $isReceipt
            ? $invoice->formatMoney((float) $invoice->amount_paid) . ' paid on ' . optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y')
            : $invoice->formatMoney($invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->total) . ' due ' . optional($invoice->due_date)->format('F j, Y');
    @endphp

    <table>
        <tr>
            <td>
                <div class="title">{{ $isReceipt ? 'Receipt' : 'Invoice' }}</div>
                <div style="margin-top:10px;line-height:1.7;">
                    <span class="label">Invoice number</span> &nbsp; <strong>{{ $invoice->invoice_number }}</strong><br>
                    @if($isReceipt)
                        <span class="label">Receipt number</span> &nbsp; <strong>{{ $invoice->invoice_number }}</strong><br>
                        <span class="label">Date paid</span> &nbsp; <strong>{{ optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y') }}</strong>
                    @else
                        <span class="label">Date of issue</span> &nbsp; <strong>{{ optional($invoice->issue_date)->format('F j, Y') }}</strong><br>
                        <span class="label">Date due</span> &nbsp; <strong>{{ optional($invoice->due_date)->format('F j, Y') }}</strong>
                    @endif
                </div>
            </td>
            <td class="logo" valign="top">{{ $brand }}</td>
        </tr>
    </table>

    <table style="margin-top:24px;">
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
                @if($invoice->student)<br><span class="muted">Student: {{ $invoice->student->name }}</span>@endif
            </td>
        </tr>
    </table>

    <div class="headline">{{ $headline }}</div>

    <table class="items">
        <thead>
            <tr>
                <th width="48%">Description</th>
                <th class="right" width="12%">Qty</th>
                <th class="right" width="20%">Unit price</th>
                <th class="right" width="20%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td class="right">{{ $invoice->formatMoney((float) $item->unit_price) }}</td>
                    <td class="right">{{ $invoice->formatMoney((float) $item->total) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No line items</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ $invoice->formatMoney((float) $invoice->subtotal) }}</td></tr>
        <tr><td>Tax</td><td class="right">{{ $invoice->formatMoney((float) $invoice->tax_amount) }}</td></tr>
        <tr><td>Total</td><td class="right">{{ $invoice->formattedTotal() }}</td></tr>
        <tr class="strong"><td>{{ $isReceipt ? 'Amount paid' : 'Amount due' }}</td><td class="right">{{ $isReceipt ? $invoice->formatMoney((float) $invoice->amount_paid) : $invoice->formatMoney($invoice->balance()) }}</td></tr>
    </table>

    @if($invoice->payments->isNotEmpty())
        <h2>Payment history</h2>
        <table class="items">
            <thead>
                <tr>
                    <th>Payment method</th>
                    <th>Date</th>
                    <th>Reference</th>
                    <th class="right">Amount paid</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ str_replace('_', ' ', ucfirst((string) $payment->method)) }}</td>
                        <td>{{ optional($payment->paid_at ?? $payment->created_at)->format('F j, Y') }}</td>
                        <td>{{ $payment->reference ?: $invoice->invoice_number }}</td>
                        <td class="right">{{ $invoice->formatMoney((float) $payment->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!$isReceipt && $settings->hasBankDetails() && $invoice->isPayable())
        <div class="note">
            Pay by bank transfer to {{ $settings->bank_name }}, {{ $settings->bank_account_title }},
            A/C {{ $settings->bank_account_number }}. Use {{ $invoice->invoice_number }} as the payment reference.
        </div>
    @endif
</body>
</html>
