<?php $__env->startSection('content'); ?>

<div class="notification-page">
    <div class="notification-list-card">
        <div class="notification-list-card__header">
            <h4 class="notification-page__title mb-0">Notification History</h4>
            <?php if($notifications->total() > 0): ?>
                <a href="<?php echo e(route('notifications.clear')); ?>"
                    class="btn btn-sm btn-outline-danger notification-clear-btn">
                    <i data-lucide="trash-2" class="icon-xs"></i>
                    Clear all
                </a>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table notification-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $notificationType = strtolower($notification->type ?? 'info');
                        ?>

                        <tr class="<?php echo e($notification->is_read ? '' : 'notification-table__row--unread'); ?>">
                            <td>
                                <a href="<?php echo e(route('notifications.show', $notification)); ?>" class="notification-title-cell text-decoration-none text-body">
                                    <?php if (! ($notification->is_read)): ?>
                                        <span class="notification-row-dot"></span>
                                    <?php endif; ?>
                                    <span><?php echo e($notification->title); ?></span>
                                </a>
                            </td>
                            <td class="notification-message-cell">
                                <a href="<?php echo e(route('notifications.show', $notification)); ?>" class="text-decoration-none text-body">
                                    <?php echo e($notification->message); ?>

                                </a>
                            </td>
                            <td>
                                <span class="notification-type-pill notification-type-pill--<?php echo e($notificationType); ?>">
                                    <?php echo e(ucfirst($notification->type ?? 'info')); ?>

                                </span>
                            </td>
                            <td>
                                <span class="notification-status-pill <?php echo e($notification->is_read ? 'is-read' : 'is-unread'); ?>">
                                    <?php echo e($notification->is_read ? 'Read' : 'Unread'); ?>

                                </span>
                            </td>
                            <td>
                                <div class="notification-date">
                                    <strong><?php echo e($notification->created_at->format('d M Y')); ?></strong>
                                    <span><?php echo e($notification->created_at->format('h:i A')); ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo e(route('notifications.show', $notification)); ?>" class="action-btn action-btn-view me-1" title="View">
                                    <i data-lucide="eye"></i>
                                </a>
                                <form action="<?php echo e(route('notifications.destroy', $notification)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="action-btn action-btn-delete" title="Delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6">
                                <div class="notification-empty-state notification-empty-state--page">
                                    <span class="notification-empty-state__icon">
                                        <i data-lucide="bell-off"></i>
                                    </span>
                                    <p class="mb-1 fw-semibold">No notifications found</p>
                                    <span>New alerts will appear here when there is activity.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $notifications,'label' => 'notifications','class' => 'notification-pagination']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($notifications),'label' => 'notifications','class' => 'notification-pagination']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal38def3cf8acaa014b33af3e7e7449e67)): ?>
<?php $attributes = $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67; ?>
<?php unset($__attributesOriginal38def3cf8acaa014b33af3e7e7449e67); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal38def3cf8acaa014b33af3e7e7449e67)): ?>
<?php $component = $__componentOriginal38def3cf8acaa014b33af3e7e7449e67; ?>
<?php unset($__componentOriginal38def3cf8acaa014b33af3e7e7449e67); ?>
<?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/notifications/index.blade.php ENDPATH**/ ?>