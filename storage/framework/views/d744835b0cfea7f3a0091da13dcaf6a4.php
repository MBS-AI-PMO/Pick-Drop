<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">SOS #<?php echo e($alert->id); ?></h4>
    <p class="text-secondary mb-0"><?php echo e($alert->user?->name ?? 'Unknown'); ?> · <?php echo e($alert->created_at?->format('d M Y, h:i A')); ?></p>
  </div>
  <a href="<?php echo e(route('sos.index')); ?>" class="btn btn-outline-secondary">Back</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header"><h6 class="mb-0">Alert</h6></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">From</label>
            <div class="fw-semibold"><?php echo e($alert->user?->name); ?></div>
            <small class="text-muted"><?php echo e($alert->user?->phone ?: $alert->user?->email); ?></small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Status</label>
            <div class="fw-semibold"><?php echo e(ucfirst($alert->status)); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Type</label>
            <div class="fw-semibold"><?php echo e($alert->type ? ucfirst($alert->type) : '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Request</label>
            <div>
              <?php if($alert->pickup_request_id): ?>
                <a href="<?php echo e(route('pickup-requests.show', $alert->pickup_request_id)); ?>">#<?php echo e($alert->pickup_request_id); ?></a>
              <?php else: ?>
                —
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Location</label>
            <div class="fw-semibold">
              <?php if($alert->lat && $alert->lng): ?>
                <?php echo e($alert->lat); ?>, <?php echo e($alert->lng); ?>

                <a class="ms-1" target="_blank" href="https://maps.google.com/?q=<?php echo e($alert->lat); ?>,<?php echo e($alert->lng); ?>">Map</a>
              <?php else: ?>
                —
              <?php endif; ?>
            </div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Message</label>
            <div><?php echo e($alert->message ?: '—'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><h6 class="mb-0">Actions</h6></div>
      <div class="card-body">
        <?php if($alert->status === 'open'): ?>
          <form method="POST" action="<?php echo e(route('sos.acknowledge', $alert)); ?>" class="mb-2">
            <?php echo csrf_field(); ?>
            <button class="btn btn-outline-dark w-100" type="submit">Acknowledge</button>
          </form>
        <?php endif; ?>
        <?php if($alert->status !== 'resolved' && $alert->user?->role === 'driver'): ?>
          <form method="POST" action="<?php echo e(route('sos.end-trip', $alert)); ?>" class="mb-2">
            <?php echo csrf_field(); ?>
            <button class="btn btn-danger w-100" type="submit">End trip</button>
          </form>
          <p class="text-muted small">The driver cannot end this trip. Ending it here closes today's trip.</p>
        <?php endif; ?>
        <?php if($alert->status !== 'resolved'): ?>
          <form method="POST" action="<?php echo e(route('sos.resolve', $alert)); ?>" class="mb-2">
            <?php echo csrf_field(); ?>
            <button class="btn btn-dark w-100" type="submit">Mark resolved</button>
          </form>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('sos.destroy', $alert)); ?>" onsubmit="confirmDelete(event, this)">
          <?php echo csrf_field(); ?>
          <?php echo method_field('DELETE'); ?>
          <button class="btn btn-outline-danger w-100" type="submit">Delete</button>
        </form>
        <?php if($alert->handler): ?>
          <small class="text-muted d-block mt-2">Handled by <?php echo e($alert->handler->name); ?></small>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/sos/show.blade.php ENDPATH**/ ?>