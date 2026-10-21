<?php $__env->startSection('content'); ?>

<div class="notification-page">
    <div class="notification-list-card">
        <div class="notification-list-card__header">
            <h4 class="notification-page__title mb-0">Notification</h4>
            <a href="<?php echo e(route('notifications.index')); ?>" class="btn btn-sm btn-outline-secondary">
                <i data-lucide="arrow-left" class="icon-xs"></i>
                Back to history
            </a>
        </div>

        <div class="p-4">
            <?php
                $notificationType = strtolower($notification->type ?? 'info');
            ?>

            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="notification-type-pill notification-type-pill--<?php echo e($notificationType); ?>">
                    <?php echo e(ucfirst($notification->type ?? 'info')); ?>

                </span>
                <span class="notification-status-pill <?php echo e($notification->is_read ? 'is-read' : 'is-unread'); ?>">
                    <?php echo e($notification->is_read ? 'Read' : 'Unread'); ?>

                </span>
            </div>

            <h5 class="fw-bold mb-2"><?php echo e($notification->title); ?></h5>
            <p class="text-secondary mb-4"><?php echo e($notification->message); ?></p>

            <div class="notification-date">
                <strong><?php echo e($notification->created_at->format('d M Y')); ?></strong>
                <span><?php echo e($notification->created_at->format('h:i A')); ?></span>
            </div>

            <div class="mt-4">
                <form action="<?php echo e(route('notifications.destroy', $notification)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger notification-clear-btn">
                        <i data-lucide="trash-2" class="icon-xs"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/notifications/show.blade.php ENDPATH**/ ?>