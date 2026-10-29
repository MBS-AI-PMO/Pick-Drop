<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
  <div>
    <h4 class="mb-1">Driver payroll</h4>
    <p class="text-secondary mb-0">Pay at month end for days the driver actually worked. Leave and no-shows are cut.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary" href="<?php echo e(route('payrolls.index', ['month' => $prev])); ?>">← <?php echo e(\Illuminate\Support\Carbon::createFromFormat('Y-m', $prev)->format('M Y')); ?></a>
    <a class="btn btn-outline-secondary" href="<?php echo e(route('payrolls.index', ['month' => $next])); ?>"><?php echo e(\Illuminate\Support\Carbon::createFromFormat('Y-m', $next)->format('M Y')); ?> →</a>
    <form method="POST" action="<?php echo e(route('payrolls.recalculate')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="month" value="<?php echo e($month); ?>">
      <button class="btn btn-outline-dark" type="submit">Refresh numbers</button>
    </form>
  </div>
</div>

<ul class="nav nav-pills gap-2 mb-4">
  <li class="nav-item">
    <a class="nav-link" href="<?php echo e(route('driver-payroll.index')); ?>">Payment bills</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="<?php echo e(route('payrolls.index', ['month' => $month])); ?>">Monthly payroll</a>
  </li>
</ul>

<div class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-3 align-items-center">
      <div class="col-lg-4">
        <div class="fw-semibold"><?php echo e($cursor->format('F Y')); ?></div>
        <div class="text-secondary small">
          <?php if($monthEnded): ?>
            Month has ended. Open each driver, check days, then pay.
          <?php else: ?>
            Month is still running. Days left are not paid yet. Pay after <?php echo e($cursor->copy()->endOfMonth()->format('d M')); ?>.
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="d-flex flex-wrap gap-4 small">
          <div>
            <div class="text-secondary">1. Days happen</div>
            <div class="fw-semibold">Present / leave / no-show</div>
          </div>
          <div>
            <div class="text-secondary">2. You check</div>
            <div class="fw-semibold">Open a driver and fix any day</div>
          </div>
          <div>
            <div class="text-secondary">3. You pay</div>
            <div class="fw-semibold">Once, at month end</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Present so far</div>
      <div class="fw-bold fs-4"><?php echo e($totals['present']); ?></div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Days still left</div>
      <div class="fw-bold fs-4"><?php echo e($totals['upcoming']); ?></div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small">Cut (leave + no-show)</div>
      <div class="fw-bold fs-4"><?php echo e($totals['cut']); ?></div>
      <div class="text-secondary small">PKR <?php echo e(number_format($totals['deduction'], 0)); ?></div>
    </div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100"><div class="card-body">
      <div class="text-secondary small"><?php echo e($monthEnded ? 'To pay now' : 'Earned so far'); ?></div>
      <div class="fw-bold fs-4">PKR <?php echo e(number_format($totals['earned'], 0)); ?></div>
      <?php if (! ($monthEnded)): ?>
        <div class="text-secondary small">If they finish remaining: PKR <?php echo e(number_format($totals['expected'], 0)); ?></div>
      <?php endif; ?>
    </div></div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th class="ps-3">Driver</th>
            <th>Present</th>
            <th>Left</th>
            <th>Cut</th>
            <th>Earned so far</th>
            <th>If they finish remaining</th>
            <th>Status</th>
            <th class="text-center pe-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td class="ps-3">
                <div class="fw-semibold"><?php echo e($payroll->driver?->name ?? 'Driver'); ?></div>
                <div class="text-secondary small">
                  <?php echo e($payroll->items_count); ?> student<?php echo e($payroll->items_count === 1 ? '' : 's'); ?>

                  <?php if((float) $payroll->daily_rate > 0): ?>
                    · daily ~ PKR <?php echo e(number_format($payroll->daily_rate, 0)); ?>

                  <?php else: ?>
                    · no monthly rate set, so pay is PKR 0
                  <?php endif; ?>
                </div>
              </td>
              <td><?php echo e($payroll->worked_days); ?></td>
              <td><?php echo e($payroll->upcoming_days); ?></td>
              <td>
                <div><?php echo e($payroll->leave_days + $payroll->absent_days); ?> days</div>
                <?php if($payroll->deduction_note): ?>
                  <div class="text-secondary small"><?php echo e($payroll->deduction_note); ?></div>
                <?php elseif($payroll->leave_days + $payroll->absent_days > 0): ?>
                  <div class="text-secondary small">PKR <?php echo e(number_format($payroll->deduction, 0)); ?> cut</div>
                <?php endif; ?>
              </td>
              <td class="fw-semibold">PKR <?php echo e(number_format($payroll->net, 0)); ?></td>
              <td>PKR <?php echo e(number_format($payroll->expected_net, 0)); ?></td>
              <td>
                <?php
                  $phase = $payroll->phase();
                  $style = match ($phase) {
                    'paid' => 'background:#d1fae5;color:#065f46;',
                    'approved', 'ready' => 'background:#dbeafe;color:#1e40af;',
                    default => 'background:#fef9c3;color:#92400e;',
                  };
                ?>
                <span class="badge rounded-pill px-3 py-2" style="<?php echo e($style); ?>"><?php echo e($payroll->phaseLabel()); ?></span>
              </td>
              <td class="text-center pe-3">
                <div class="action-btns justify-content-center">
                  <a href="<?php echo e(route('payrolls.show', $payroll)); ?>" class="action-btn action-btn-view" title="Manage days">
                    <i data-lucide="eye"></i>
                  </a>
                  <form action="<?php echo e(route('payrolls.destroy', $payroll)); ?>" method="POST"
                        class="d-inline" onsubmit="confirmDelete(event, this)">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="action-btn action-btn-delete" title="Delete">
                      <i data-lucide="trash-2"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="8" class="text-center text-secondary py-4">
                No paid shifts for this month yet. When a parent pays and a driver is assigned, they appear here after Refresh.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/payrolls/index.blade.php ENDPATH**/ ?>