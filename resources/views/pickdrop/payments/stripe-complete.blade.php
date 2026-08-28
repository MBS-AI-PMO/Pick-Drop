<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $success ? 'Payment successful' : 'Payment update' }}</title>
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f7f9fc; color:#1f2937; margin:0; padding:40px 16px; }
    .card { max-width:480px; margin:40px auto; background:#fff; border:1px solid #e6ebf2; border-radius:12px; padding:32px; text-align:center; }
    h1 { font-size:22px; margin:0 0 8px; }
    p { color:#697586; }
    .ok { color:#111111; }
    .warn { color:#92400e; }
  </style>
</head>
<body>
  <div class="card">
    <h1 class="{{ $success ? 'ok' : 'warn' }}">{{ $success ? 'Payment successful' : 'Payment update' }}</h1>
    <p>{{ $message }}</p>
    @if($invoice)
      <p><strong>{{ $invoice->invoice_number }}</strong><br>{{ $invoice->formattedTotal() }}</p>
    @endif
    <p>You can close this window and return to the PickDrop app.</p>
  </div>
</body>
</html>
