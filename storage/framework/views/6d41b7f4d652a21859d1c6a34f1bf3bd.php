<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p><?php echo e(__('Hi :name,', ['name' => $userName])); ?></p>
    <p><?php echo e(__('Your email verification code is:')); ?> <strong style="font-size: 18px; letter-spacing: 2px;"><?php echo e($code); ?></strong></p>
    <p><?php echo e(__('This code expires in 30 minutes. If you did not request this, you can ignore this email.')); ?></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/mail/email-verification-code.blade.php ENDPATH**/ ?>