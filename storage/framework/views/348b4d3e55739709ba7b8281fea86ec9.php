<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p><?php echo e(__('Hi :name,', ['name' => $userName])); ?></p>
    <p><?php echo e(__('Your identity verification has been approved. Your PickDrop account is now verified.')); ?></p>
    <?php if($isSelf): ?>
        <p><?php echo e(__('Next, add your pickup location, drop / office location, and office timing in the app.')); ?></p>
    <?php else: ?>
        <p><?php echo e(__('Next, add your children details in the app so we can arrange their pick and drop.')); ?></p>
    <?php endif; ?>
    <p><?php echo e(__('If you did not create this account, please contact support.')); ?></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/mail/account-verified.blade.php ENDPATH**/ ?>