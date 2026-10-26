<?php $__env->startSection('page-content-class', 'container-fluid'); ?>

<?php
  $badge = function (string $status) {
      return match ($status) {
          'received' => ['Received', 'background:#d1fae5;color:#065f46;'],
          'not_received' => ['Not received', 'background:#fee2e2;color:#991b1b;'],
          default => ['Pending', 'background:#fef9c3;color:#92400e;'],
      };
  };
?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Payment screenshots</h4>
    <p class="text-secondary mb-0">Screenshots customers send after paying. Mark each one received or not received.</p>
  </div>
  <a href="<?php echo e(route('payments.index')); ?>" class="btn btn-outline-secondary">Back to invoices</a>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('payments.screenshots')); ?>" class="row g-2 align-items-center">
      <div class="col-12 col-md-3">
        <select class="form-select" name="receipt_status" onchange="this.form.submit()">
          <option value="">All statuses</option>
          <option value="not_received" <?php echo e(request('receipt_status') === 'not_received' ? 'selected' : ''); ?>>Not received</option>
          <option value="received" <?php echo e(request('receipt_status') === 'received' ? 'selected' : ''); ?>>Received</option>
        </select>
      </div>
      <?php if(request()->filled('receipt_status')): ?>
        <div class="col-auto">
          <a href="<?php echo e(route('payments.screenshots')); ?>" class="btn btn-outline-danger">Clear</a>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 w-100">
        <thead class="table-light">
          <tr>
            <th class="ps-4 py-3">Screenshot</th>
            <th class="py-3">Invoice</th>
            <th class="py-3">Customer</th>
            <th class="py-3">Method</th>
            <th class="py-3">Amount</th>
            <th class="py-3">Sent</th>
            <th class="py-3">Status</th>
            <th class="py-3 text-end pe-4">Update</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php [$label, $style] = $badge($payment->receiptStatus()); ?>
            <tr>
              <td class="ps-4">
                <?php if($payment->proofUrl()): ?>
                  <button type="button"
                          class="btn p-0 border-0 bg-transparent screenshot-thumb"
                          data-bs-toggle="modal"
                          data-bs-target="#paymentScreenshotModal"
                          data-screenshot-url="<?php echo e($payment->proofUrl()); ?>"
                          data-screenshot-title="<?php echo e($payment->invoice?->invoice_number ?: 'Payment screenshot'); ?>"
                          title="View screenshot">
                    <img src="<?php echo e($payment->proofUrl()); ?>" alt="Payment screenshot" style="width:72px;height:72px;object-fit:cover;border-radius:8px;cursor:pointer;">
                  </button>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <?php if($payment->invoice): ?>
                  <a href="<?php echo e(route('payments.show', $payment->invoice)); ?>" class="fw-semibold"><?php echo e($payment->invoice->invoice_number); ?></a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <div><?php echo e($payment->user?->name ?? $payment->invoice?->customer?->name ?? '—'); ?></div>
                <small class="text-muted"><?php echo e($payment->reference ?: 'No reference'); ?></small>
              </td>
              <td><?php echo e(str_replace('_', ' ', $payment->method)); ?></td>
              <td class="fw-semibold"><?php echo e($payment->invoice?->formatMoney((float) $payment->amount) ?? number_format((float) $payment->amount, 2)); ?></td>
              <td><?php echo e($payment->created_at?->format('d M Y, h:i A')); ?></td>
              <td><span class="badge rounded-pill px-3 py-1" style="<?php echo e($style); ?>"><?php echo e($label); ?></span></td>
              <td class="text-end pe-4">
                <form method="POST" action="<?php echo e(route('payments.receipt', $payment)); ?>" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="receipt_status" value="received">
                  <button class="btn btn-sm btn-dark" type="submit" <?php echo e($payment->receiptStatus() === 'received' ? 'disabled' : ''); ?>>Received</button>
                </form>
                <form method="POST" action="<?php echo e(route('payments.receipt', $payment)); ?>" class="d-inline">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="receipt_status" value="not_received">
                  <button class="btn btn-sm btn-outline-danger" type="submit" <?php echo e($payment->receiptStatus() === 'not_received' ? 'disabled' : ''); ?>>Not received</button>
                </form>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-muted">No payment screenshots yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $payments,'label' => 'screenshots']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($payments),'label' => 'screenshots']); ?>
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


<div class="modal fade" id="paymentScreenshotModal" tabindex="-1" aria-labelledby="paymentScreenshotModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="paymentScreenshotModalLabel">Payment screenshot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-3 text-center">
        <img id="paymentScreenshotModalImg" src="" alt="Payment screenshot"
             class="img-fluid rounded-3"
             style="max-height:75vh;width:auto;object-fit:contain;">
      </div>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('paymentScreenshotModal');
  if (!modalEl) return;

  modalEl.addEventListener('show.bs.modal', function (event) {
    var trigger = event.relatedTarget;
    if (!trigger) return;

    var url = trigger.getAttribute('data-screenshot-url') || '';
    var title = trigger.getAttribute('data-screenshot-title') || 'Payment screenshot';
    var img = document.getElementById('paymentScreenshotModalImg');
    var label = document.getElementById('paymentScreenshotModalLabel');

    if (img) img.src = url;
    if (label) label.textContent = title;
  });

  modalEl.addEventListener('hidden.bs.modal', function () {
    var img = document.getElementById('paymentScreenshotModalImg');
    if (img) img.src = '';
  });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/payments/screenshots.blade.php ENDPATH**/ ?>