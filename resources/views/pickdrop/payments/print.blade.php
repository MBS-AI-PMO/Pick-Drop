<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $invoice->isPaid() ? 'Receipt' : 'Invoice' }} {{ $invoice->invoice_number }}</title>
  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
      font-family: Arial, Helvetica, sans-serif;
      color: #111111;
      background: #ffffff;
      min-height: 100vh;
    }
    .toolbar {
      max-width: 760px;
      margin: 0 auto;
      padding: 20px 24px 0;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }
    .toolbar a, .toolbar button {
      display: inline-flex;
      align-items: center;
      height: 38px;
      padding: 0 14px;
      border: 1px solid #111111;
      border-radius: 8px;
      background: #ffffff;
      color: #111111;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .page {
      max-width: 760px;
      margin: 8px auto 48px;
      padding: 28px 24px 48px;
    }
    .top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 28px;
    }
    .title { font-size: 34px; font-weight: 700; margin: 0; letter-spacing: -0.4px; }
    .logo { font-size: 20px; font-weight: 700; }
    .meta { margin: 0; padding: 0; list-style: none; font-size: 14px; line-height: 1.7; }
    .meta span { color: #6b7280; display: inline-block; min-width: 130px; }
    .parties {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 32px;
      margin: 36px 0 28px;
      font-size: 14px;
      line-height: 1.55;
    }
    .parties h3 { margin: 0 0 8px; font-size: 14px; }
    .muted { color: #6b7280; }
    .headline { font-size: 22px; font-weight: 700; margin: 0 0 28px; }
    table { width: 100%; border-collapse: collapse; }
    .items th {
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      text-align: left;
      padding: 0 0 8px;
      border-bottom: 1px solid #111111;
    }
    .items td { padding: 12px 0; font-size: 14px; border-bottom: 1px solid #ececec; vertical-align: top; }
    .right { text-align: right; white-space: nowrap; }
    .totals { width: 280px; margin-left: auto; margin-top: 8px; }
    .totals td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #ececec; }
    .totals .strong td { border-bottom: 0; font-weight: 700; padding-top: 12px; }
    h2 { font-size: 16px; margin: 36px 0 12px; }
    .pay-note { margin-top: 24px; font-size: 13px; color: #6b7280; line-height: 1.6; }
    @media print {
      .toolbar { display: none !important; }
      .page { margin: 0; padding: 0; max-width: none; }
    }
  </style>
</head>
<body>
  @php
    $isReceipt = $invoice->isPaid();
    $headline = $isReceipt
      ? $invoice->formatMoney((float) $invoice->amount_paid) . ' paid on ' . optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y')
      : $invoice->formatMoney($invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->total) . ' due ' . optional($invoice->due_date)->format('F j, Y');
  @endphp

  <div class="toolbar">
    <a href="{{ route('payments.show', $invoice) }}">Back</a>
    <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>

  <div class="page">
    <div class="top">
      <div>
        <h1 class="title">{{ $isReceipt ? 'Receipt' : 'Invoice' }}</h1>
        <ul class="meta">
          <li><span>Invoice number</span> <strong>{{ $invoice->invoice_number }}</strong></li>
          @if($isReceipt)
            <li><span>Receipt number</span> <strong>{{ $invoice->invoice_number }}</strong></li>
            <li><span>Date paid</span> <strong>{{ optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y') }}</strong></li>
          @else
            <li><span>Date of issue</span> <strong>{{ optional($invoice->issue_date)->format('F j, Y') }}</strong></li>
            <li><span>Date due</span> <strong>{{ optional($invoice->due_date)->format('F j, Y') }}</strong></li>
          @endif
        </ul>
      </div>
      <div class="logo">{{ $settings->company_name ?: 'PickDrop' }}</div>
    </div>

    <div class="parties">
      <div>
        <h3>{{ $settings->company_name ?: 'PickDrop' }}</h3>
        @if($settings->company_address)<div>{{ $settings->company_address }}</div>@endif
        @if($settings->company_email)<div>{{ $settings->company_email }}</div>@endif
        @if($settings->company_phone)<div>{{ $settings->company_phone }}</div>@endif
      </div>
      <div>
        <h3>Bill to</h3>
        <div>{{ $invoice->customer?->name ?: 'Customer' }}</div>
        <div>{{ $invoice->customer?->email }}</div>
        @if($invoice->student)<div class="muted">Student: {{ $invoice->student->name }}</div>@endif
      </div>
    </div>

    <p class="headline">{{ $headline }}</p>

    <table class="items">
      <thead>
        <tr>
          <th>Description</th>
          <th class="right">Qty</th>
          <th class="right">Unit price</th>
          <th class="right">Amount</th>
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
      <tr><td>Tax{{ $invoice->tax_percent ? ' ('.rtrim(rtrim(number_format($invoice->tax_percent, 2), '0'), '.').'%)' : '' }}</td><td class="right">{{ $invoice->formatMoney((float) $invoice->tax_amount) }}</td></tr>
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

    @if($settings->hasBankDetails() && $invoice->isPayable())
      <div class="pay-note">
        Pay by bank transfer to {{ $settings->bank_name }}, {{ $settings->bank_account_title }},
        A/C {{ $settings->bank_account_number }}@if($settings->bank_iban), IBAN {{ $settings->bank_iban }}@endif.
        Use {{ $invoice->invoice_number }} as the payment reference.
      </div>
    @endif

    @if($invoice->notes)
      <div class="pay-note">{{ $invoice->notes }}</div>
    @endif
  </div>
</body>
</html>
