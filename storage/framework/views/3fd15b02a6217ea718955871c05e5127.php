<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($brand); ?> <?php echo e($isReceipt ? 'Receipt' : 'Invoice'); ?> <?php echo e($invoice->invoice_number); ?></title>
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
    <?php
        $headline = $isReceipt
            ? $invoice->formatMoney((float) $invoice->amount_paid) . ' paid on ' . optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y')
            : $invoice->formatMoney($invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->total) . ' due ' . optional($invoice->due_date)->format('F j, Y');
    ?>

    <table>
        <tr>
            <td>
                <div class="title"><?php echo e($isReceipt ? 'Receipt' : 'Invoice'); ?></div>
                <div style="margin-top:10px;line-height:1.7;">
                    <span class="label">Invoice number</span> &nbsp; <strong><?php echo e($invoice->invoice_number); ?></strong><br>
                    <?php if($isReceipt): ?>
                        <span class="label">Receipt number</span> &nbsp; <strong><?php echo e($invoice->invoice_number); ?></strong><br>
                        <span class="label">Date paid</span> &nbsp; <strong><?php echo e(optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y')); ?></strong>
                    <?php else: ?>
                        <span class="label">Date of issue</span> &nbsp; <strong><?php echo e(optional($invoice->issue_date)->format('F j, Y')); ?></strong><br>
                        <span class="label">Date due</span> &nbsp; <strong><?php echo e(optional($invoice->due_date)->format('F j, Y')); ?></strong>
                    <?php endif; ?>
                </div>
            </td>
            <td class="logo" valign="top"><?php echo e($brand); ?></td>
        </tr>
    </table>

    <table style="margin-top:24px;">
        <tr>
            <td width="50%" valign="top">
                <strong><?php echo e($settings->company_name ?: 'PickDrop'); ?></strong><br>
                <?php if($settings->company_address): ?><?php echo e($settings->company_address); ?><br><?php endif; ?>
                <?php echo e($settings->company_email); ?>

                <?php if($settings->company_phone): ?><br><?php echo e($settings->company_phone); ?><?php endif; ?>
            </td>
            <td width="50%" valign="top">
                <strong>Bill to</strong><br>
                <?php echo e($invoice->customer?->name ?: 'Customer'); ?><br>
                <?php echo e($invoice->customer?->email); ?>

                <?php if($invoice->student): ?><br><span class="muted">Student: <?php echo e($invoice->student->name); ?></span><?php endif; ?>
            </td>
        </tr>
    </table>

    <div class="headline"><?php echo e($headline); ?></div>

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
            <?php $__empty_1 = true; $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($item->description); ?></td>
                    <td class="right"><?php echo e(rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.')); ?></td>
                    <td class="right"><?php echo e($invoice->formatMoney((float) $item->unit_price)); ?></td>
                    <td class="right"><?php echo e($invoice->formatMoney((float) $item->total)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4">No line items</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right"><?php echo e($invoice->formatMoney((float) $invoice->subtotal)); ?></td></tr>
        <tr><td>Tax</td><td class="right"><?php echo e($invoice->formatMoney((float) $invoice->tax_amount)); ?></td></tr>
        <tr><td>Total</td><td class="right"><?php echo e($invoice->formattedTotal()); ?></td></tr>
        <tr class="strong"><td><?php echo e($isReceipt ? 'Amount paid' : 'Amount due'); ?></td><td class="right"><?php echo e($isReceipt ? $invoice->formatMoney((float) $invoice->amount_paid) : $invoice->formatMoney($invoice->balance())); ?></td></tr>
    </table>

    <?php if($invoice->payments->isNotEmpty()): ?>
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
                <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(str_replace('_', ' ', ucfirst((string) $payment->method))); ?></td>
                        <td><?php echo e(optional($payment->paid_at ?? $payment->created_at)->format('F j, Y')); ?></td>
                        <td><?php echo e($payment->reference ?: $invoice->invoice_number); ?></td>
                        <td class="right"><?php echo e($invoice->formatMoney((float) $payment->amount)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if(!$isReceipt && $settings->hasBankDetails() && $invoice->isPayable()): ?>
        <div class="note">
            Pay by bank transfer to <?php echo e($settings->bank_name); ?>, <?php echo e($settings->bank_account_title); ?>,
            A/C <?php echo e($settings->bank_account_number); ?>. Use <?php echo e($invoice->invoice_number); ?> as the payment reference.
        </div>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/mail/invoice-pdf.blade.php ENDPATH**/ ?>