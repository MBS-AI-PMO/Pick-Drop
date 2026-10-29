<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title><?php echo e($isReceipt ? 'Receipt' : 'Invoice'); ?> <?php echo e($invoice->invoice_number); ?></title>
</head>
<body style="margin:0;padding:0;width:100%;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111111;line-height:1.5;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#ffffff;">
        <tr>
            <td align="center" style="padding:16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;">
                    <tr>
                        <td style="padding:0;word-break:break-word;overflow-wrap:anywhere;">
                            <p style="margin:0 0 16px;font-size:16px;">Hi <?php echo e($invoice->customer?->name ?? 'there'); ?>,</p>
                            <?php if($isReceipt): ?>
                                <p style="margin:0 0 20px;font-size:15px;">We received your payment to our bank account. Your invoice PDF and receipt PDF are attached.</p>
                            <?php elseif(!empty($pendingBank)): ?>
                                <p style="margin:0 0 20px;font-size:15px;">We received your bank transfer to <?php echo e($settings->bank_name ?: 'our PickDrop account'); ?>. Your invoice PDF is attached. We will confirm the payment once the amount is verified.</p>
                            <?php else: ?>
                                <p style="margin:0 0 20px;font-size:15px;">Your invoice PDF is attached. Pay by bank transfer using the account below. Payment is due by <?php echo e($invoice->due_date?->format('F j, Y')); ?>.</p>
                            <?php endif; ?>

                            <p style="margin:0;font-size:26px;font-weight:bold;line-height:1.2;"><?php echo e($isReceipt ? 'Receipt' : 'Invoice'); ?></p>
                            <p style="margin:6px 0 0;font-size:16px;font-weight:bold;">PickDrop</p>

                            <p style="margin:14px 0 0;font-size:14px;line-height:1.7;">
                                <span style="color:#6b7280;">Invoice number</span><br>
                                <strong><?php echo e($invoice->invoice_number); ?></strong><br>
                                <?php if($isReceipt): ?>
                                    <span style="color:#6b7280;">Date paid</span><br>
                                    <strong><?php echo e(optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y')); ?></strong>
                                <?php else: ?>
                                    <span style="color:#6b7280;">Date of issue</span><br>
                                    <strong><?php echo e(optional($invoice->issue_date)->format('F j, Y')); ?></strong><br>
                                    <span style="color:#6b7280;">Date due</span><br>
                                    <strong><?php echo e(optional($invoice->due_date)->format('F j, Y')); ?></strong>
                                <?php endif; ?>
                            </p>

                            <p style="margin:18px 0 0;font-size:14px;line-height:1.7;">
                                <strong><?php echo e($settings->company_name ?: 'PickDrop'); ?></strong><br>
                                <?php if($settings->company_address): ?><?php echo e($settings->company_address); ?><br><?php endif; ?>
                                <?php echo e($settings->company_email); ?>

                                <?php if($settings->company_phone): ?><br><?php echo e($settings->company_phone); ?><?php endif; ?>
                            </p>

                            <p style="margin:14px 0 0;font-size:14px;line-height:1.7;">
                                <strong>Bill to</strong><br>
                                <?php echo e($invoice->customer?->name ?: 'Customer'); ?><br>
                                <?php echo e($invoice->customer?->email); ?>

                                <?php if($invoice->student): ?><br><span style="color:#6b7280;">Student: <?php echo e($invoice->student->name); ?></span><?php endif; ?>
                            </p>

                            <p style="font-size:16px;font-weight:bold;margin:20px 0 14px;">
                                <?php if($isReceipt): ?>
                                    <?php echo e($invoice->formatMoney((float) $invoice->amount_paid)); ?> paid on <?php echo e(optional($invoice->paid_at ?? $invoice->issue_date)->format('F j, Y')); ?>

                                <?php else: ?>
                                    <?php echo e($invoice->formatMoney($invoice->balance() > 0 ? $invoice->balance() : (float) $invoice->total)); ?> due <?php echo e(optional($invoice->due_date)->format('F j, Y')); ?>

                                <?php endif; ?>
                            </p>

                            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-bottom:1px solid #ececec;">
                                    <tr>
                                        <td style="padding:12px 0 4px;font-size:14px;word-break:break-word;overflow-wrap:anywhere;"><?php echo e($item->description); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 12px;font-size:13px;color:#374151;word-break:break-word;">
                                            Qty <?php echo e(rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.')); ?>

                                            · <?php echo e($invoice->formatMoney((float) $item->unit_price)); ?>

                                            · <strong style="color:#111111;"><?php echo e($invoice->formatMoney((float) $item->total)); ?></strong>
                                        </td>
                                    </tr>
                                </table>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin-top:12px;font-size:14px;">
                                <tr>
                                    <td style="padding:6px 8px 6px 0;">Subtotal</td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;"><?php echo e($invoice->formatMoney((float) $invoice->subtotal)); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 8px 6px 0;">Tax</td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;"><?php echo e($invoice->formatMoney((float) $invoice->tax_amount)); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 8px 6px 0;"><strong>Total</strong></td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;"><strong><?php echo e($invoice->formattedTotal()); ?></strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 8px 6px 0;"><strong><?php echo e($isReceipt ? 'Amount paid' : 'Amount due'); ?></strong></td>
                                    <td align="right" style="padding:6px 0;white-space:nowrap;"><strong><?php echo e($isReceipt ? $invoice->formatMoney((float) $invoice->amount_paid) : $invoice->formatMoney($invoice->balance())); ?></strong></td>
                                </tr>
                            </table>

                            <?php if(!$isReceipt && $settings->hasBankDetails()): ?>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0 0;font-size:14px;background:#f9fafb;border:1px solid #ececec;">
                                    <tr>
                                        <td style="padding:14px;word-break:break-word;overflow-wrap:anywhere;line-height:1.7;">
                                            <strong>Pay to this bank account</strong><br>
                                            <?php echo e($settings->bank_name); ?><br>
                                            <?php echo e($settings->bank_account_title); ?><br>
                                            A/C <?php echo e($settings->bank_account_number); ?>

                                            <?php if($settings->bank_iban): ?><br>IBAN <?php echo e($settings->bank_iban); ?><?php endif; ?>
                                            <br>Use <strong><?php echo e($invoice->invoice_number); ?></strong> as the payment reference.
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <p style="margin:20px 0 0;font-size:13px;color:#6b7280;word-break:break-word;overflow-wrap:anywhere;">
                                <?php echo e($settings->company_name ?: 'PickDrop'); ?>

                                <?php if($settings->company_email): ?> · <?php echo e($settings->company_email); ?><?php endif; ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/mail/invoice.blade.php ENDPATH**/ ?>