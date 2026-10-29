<?php $__env->startSection('page-content-class', 'container-fluid'); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin gap-2">
  <div>
    <h4 class="mb-1">Driver Payments</h4>
    <p class="text-secondary mb-0">Review driver payment requests, generate shift-wise bills, and record payouts.</p>
  </div>
  <button type="button" class="btn btn-primary" id="newDriverBillBtn" data-bs-toggle="modal" data-bs-target="#newDriverBillModal">
    <i data-lucide="plus" class="icon-sm me-1"></i> New driver bill
  </button>
</div>

<ul class="nav nav-pills gap-2 mb-4">
  <li class="nav-item">
    <a class="nav-link active" href="<?php echo e(route('driver-payroll.index')); ?>">Payment bills</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo e(route('payrolls.index')); ?>">Monthly payroll</a>
  </li>
</ul>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Pending review</p>
        <h4 class="fw-bold text-warning mb-0"><?php echo e($summary['pending_count']); ?></h4>
        <small class="text-muted">PKR <?php echo e(number_format((float) $summary['pending'], 2)); ?></small>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Approved</p>
        <h4 class="fw-bold mb-0" style="color:#3f6fd9;">PKR <?php echo e(number_format((float) $summary['approved'], 2)); ?></h4>
        <small class="text-muted">Awaiting payout</small>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <p class="text-secondary fs-13px mb-1">Total paid</p>
        <h4 class="fw-bold text-success mb-0">PKR <?php echo e(number_format((float) $summary['paid'], 2)); ?></h4>
        <small class="text-muted">All time</small>
      </div>
    </div>
  </div>
</div>

<div class="payroll-ready mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <div>
      <h5 class="mb-1">Ready for billing</h5>
      <p class="text-secondary mb-0 fs-13px">Completed months with unpaid shifts — generate bills from attendance.</p>
    </div>
    <?php
      $pickerMonth = filled($readyMonth ?? null)
        ? \Illuminate\Support\Carbon::parse($readyMonth . '-01')
        : now()->startOfMonth();
      $pickerYear = (int) $pickerMonth->year;
      $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      $queryBase = request()->except(['ready_month', 'page', 'ready_page']);
    ?>
    <div class="payroll-month-picker" data-payroll-month-picker>
      <button type="button"
              class="payroll-month-picker__trigger"
              data-picker-toggle
              aria-expanded="false"
              aria-haspopup="true">
        <i data-lucide="calendar" class="payroll-month-picker__icon"></i>
        <span data-picker-label>
          <?php echo e(filled($readyMonth ?? null) ? $pickerMonth->format('F Y') : 'Select month'); ?>

        </span>
        <i data-lucide="chevron-down" class="payroll-month-picker__caret"></i>
      </button>
      <?php if(filled($readyMonth ?? null)): ?>
        <a href="<?php echo e(route('driver-payroll.index', $queryBase)); ?>"
           class="payroll-month-picker__clear"
           title="Clear month">
          <i data-lucide="x"></i>
        </a>
      <?php endif; ?>

      <div class="payroll-month-picker__panel" data-picker-panel hidden>
        <div class="payroll-month-picker__header">
          <button type="button" class="payroll-month-picker__nav" data-picker-prev aria-label="Previous year">
            <i data-lucide="chevron-left"></i>
          </button>
          <strong data-picker-year><?php echo e($pickerYear); ?></strong>
          <button type="button" class="payroll-month-picker__nav" data-picker-next aria-label="Next year">
            <i data-lucide="chevron-right"></i>
          </button>
        </div>
        <div class="payroll-month-picker__grid" data-picker-grid>
          <?php for($m = 1; $m <= 12; $m++): ?>
            <?php $value = sprintf('%04d-%02d', $pickerYear, $m); ?>
            <a href="<?php echo e(route('driver-payroll.index', array_merge($queryBase, ['ready_month' => $value]))); ?>"
               class="payroll-month-picker__month <?php echo e(($readyMonth ?? '') === $value ? 'is-active' : ''); ?>"
               data-month="<?php echo e($m); ?>">
              <?php echo e($monthNames[$m - 1]); ?>

            </a>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if(($groupedReady ?? null) && $groupedReady->isNotEmpty()): ?>
    <div class="row g-3">
      <?php $__currentLoopData = $groupedReady; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-12">
          <div class="payroll-ready-card">
            <div class="payroll-ready-card__main">
              <div class="payroll-ready-card__avatar">
                <?php echo e(strtoupper(substr($group['driver_name'], 0, 1))); ?>

              </div>
              <div class="payroll-ready-card__info">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                  <h6 class="mb-0"><?php echo e($group['driver_name']); ?></h6>
                  <span class="payroll-ready-badge"><?php echo e($group['period_label']); ?></span>
                </div>
                <p class="text-secondary fs-13px mb-0">
                  <?php echo e($group['driver_phone'] ?: 'No phone'); ?>

                  · <strong><?php echo e($group['shift_count']); ?></strong> total shift<?php echo e($group['shift_count'] === 1 ? '' : 's'); ?> this month
                </p>
              </div>
            </div>

            <div class="payroll-ready-card__amount">
              <span class="text-secondary fs-12px d-block mb-1">Estimated total</span>
              <strong class="fs-5">PKR <?php echo e(number_format((float) $group['total_estimated'], 2)); ?></strong>
            </div>

            <div class="payroll-ready-card__actions">
              <a href="<?php echo e(route('driver-payroll.driver', ['user' => $group['driver_id'], 'month' => $group['month']])); ?>"
                 class="btn btn-outline-secondary">
                View details
              </a>
              <form method="POST" action="<?php echo e(route('driver-payroll.generate-all', $group['driver_id'])); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="month" value="<?php echo e($group['month']); ?>">
                <button type="submit" class="btn btn-primary">Generate bills</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-3">
      <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $groupedReady,'label' => 'drivers','class' => 'border-0 px-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($groupedReady),'label' => 'drivers','class' => 'border-0 px-0']); ?>
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
  <?php else: ?>
    <div class="payroll-ready-empty">
      <i data-lucide="wallet" class="icon-lg mb-2"></i>
      <p class="fw-semibold mb-1">Nothing ready for billing</p>
      <p class="text-secondary fs-13px mb-0">
        <?php if(filled($readyMonth ?? null)): ?>
          No completed shifts for this month yet.
        <?php else: ?>
          Completed months will appear here when drivers have unpaid shifts.
        <?php endif; ?>
      </p>
    </div>
  <?php endif; ?>
</div>

<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?php echo e(route('driver-payroll.index')); ?>">
      <?php if(filled($readyMonth ?? null)): ?>
        <input type="hidden" name="ready_month" value="<?php echo e($readyMonth); ?>">
      <?php endif; ?>
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search bill, driver, student..."
                 value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($val); ?>" <?php echo e(request('status') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select class="form-select" name="bill_month" onchange="this.form.submit()">
            <option value="">All months</option>
            <?php $__currentLoopData = $billMonths ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $monthOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($monthOption['value']); ?>" <?php echo e(request('bill_month') === $monthOption['value'] ? 'selected' : ''); ?>>
                <?php echo e($monthOption['label']); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
          <?php if(request()->hasAny(['search', 'status', 'bill_month'])): ?>
            <a href="<?php echo e(route('driver-payroll.index', array_filter(['ready_month' => $readyMonth ?? null]))); ?>" class="btn btn-outline-danger">Clear</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h6 class="mb-0">Payment bills</h6>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="py-3 ps-3">Bill #</th>
          <th class="py-3">Driver</th>
          <th class="py-3">Shift / Student</th>
          <th class="py-3">Period</th>
          <th class="py-3">Amount</th>
          <th class="py-3">Status</th>
          <th class="py-3 text-end pe-3">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $bills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td class="fw-semibold ps-3"><?php echo e($bill->bill_number); ?></td>
            <td>
              <a href="<?php echo e(route('driver-payroll.driver', $bill->driver_id)); ?>" class="fw-semibold text-decoration-none">
                <?php echo e($bill->driver?->name); ?>

              </a>
            </td>
            <td>
              <div><?php echo e($bill->pickupRequest?->student?->name ?: '—'); ?></div>
              <small class="text-muted">Shift #<?php echo e($bill->pickup_request_id); ?></small>
            </td>
            <td><?php echo e($bill->period_start->format('d M')); ?> – <?php echo e($bill->period_end->format('d M Y')); ?></td>
            <td class="fw-bold"><?php echo e($bill->formattedAmount()); ?></td>
            <td>
              <span class="badge rounded-pill px-3 py-1" style="<?php echo e($bill->statusBadgeStyle()); ?>"><?php echo e($bill->statusLabel()); ?></span>
            </td>
            <td class="text-end pe-3">
              <a href="<?php echo e(route('driver-payroll.show', $bill)); ?>" class="btn btn-sm btn-outline-secondary">Open bill</a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-secondary">
              <i data-lucide="receipt" class="icon-lg mb-2 d-block mx-auto"></i>
              <p class="mb-1 fw-semibold">No payment bills yet</p>
              <span class="fs-13px">Bills appear here after generation from completed shift periods.</span>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (isset($component)) { $__componentOriginal38def3cf8acaa014b33af3e7e7449e67 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal38def3cf8acaa014b33af3e7e7449e67 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-pagination','data' => ['paginator' => $bills,'label' => 'bills']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bills),'label' => 'bills']); ?>
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


<div class="modal fade" id="newDriverBillModal" tabindex="-1" aria-labelledby="newDriverBillModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="newDriverBillModalLabel">New driver bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?php echo e(route('driver-payroll.generate')); ?>" id="newDriverBillForm" novalidate>
        <?php echo csrf_field(); ?>
        <input type="hidden" name="pickup_request_id" id="ndbRequestId">
        <input type="hidden" name="period_start" id="ndbPeriodStart">
        <input type="hidden" name="period_end" id="ndbPeriodEnd">
        <div class="modal-body pt-2">
          <p class="text-secondary small mb-3">
            Create a payment bill for one shift and month. The amount is calculated from attendance and the driver's
            monthly rate. Only completed months of shifts the customer has paid for can be billed.
          </p>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="ndbDriver">Driver <span class="text-danger">*</span></label>
            <select id="ndbDriver" class="form-select">
              <option value="">Select driver</option>
              <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?><?php if($driver->phone): ?> · <?php echo e($driver->phone); ?><?php endif; ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="ndbShift">Shift <span class="text-danger">*</span></label>
            <select id="ndbShift" class="form-select" disabled>
              <option value="">Select a driver first</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="ndbPeriod">Billing period <span class="text-danger">*</span></label>
            <select id="ndbPeriod" class="form-select" disabled>
              <option value="">Select a shift first</option>
            </select>
          </div>
          <div id="ndbEstimate" class="alert alert-light border py-2 mb-0 d-none">
            Estimated bill: <strong id="ndbEstimateAmount">—</strong>
            <span class="text-secondary small d-block">Final amount is calculated when the bill is generated; you can review it before approving.</span>
          </div>
          <div id="ndbEmpty" class="text-secondary small mt-2 d-none"></div>
        </div>
        <div class="modal-footer border-0 pt-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4" id="ndbSubmit" disabled>Generate bill</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('style'); ?>
<style>
  .payroll-month-picker {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .payroll-month-picker__trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid #e5ecf5;
    border-radius: 10px;
    background: #ffffff;
    color: #1d3557;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(29, 53, 87, 0.04);
    cursor: pointer;
  }
  .payroll-month-picker__trigger:hover {
    border-color: #d0dae8;
  }
  .payroll-month-picker__icon,
  .payroll-month-picker__caret {
    width: 16px;
    height: 16px;
    color: #64748b;
  }
  .payroll-month-picker__clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e5ecf5;
    background: #fff;
    color: #64748b;
    text-decoration: none;
  }
  .payroll-month-picker__clear:hover {
    color: #1d3557;
    border-color: #d0dae8;
  }
  .payroll-month-picker__clear i {
    width: 14px;
    height: 14px;
  }
  .payroll-month-picker__panel {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    z-index: 30;
    width: 268px;
    padding: 14px;
    border: 1px solid #edf1f7;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 16px 40px rgba(29, 53, 87, 0.12);
  }
  .payroll-month-picker__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
  }
  .payroll-month-picker__header strong {
    font-size: 14px;
    color: #1d3557;
  }
  .payroll-month-picker__nav {
    width: 30px;
    height: 30px;
    border: 0;
    border-radius: 8px;
    background: #f4f7fb;
    color: #1d3557;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .payroll-month-picker__nav:hover {
    background: #e8eef8;
  }
  .payroll-month-picker__nav i {
    width: 16px;
    height: 16px;
  }
  .payroll-month-picker__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
  }
  .payroll-month-picker__month {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
  }
  .payroll-month-picker__month:hover {
    background: #eef4ff;
    color: #3f6fd9;
  }
  .payroll-month-picker__month.is-active {
    background: #1d3557;
    color: #ffffff;
  }

  .payroll-ready-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    padding: 18px 20px;
    background: #ffffff;
    border: 1px solid #edf1f7;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(29, 53, 87, 0.04);
  }
  .payroll-ready-card__main {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 260px;
    flex: 1 1 280px;
  }
  .payroll-ready-card__avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eef4ff;
    color: #3f6fd9;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    flex: 0 0 44px;
  }
  .payroll-ready-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: #fff7e6;
    color: #b7791f;
    font-size: 11px;
    font-weight: 700;
  }
  .payroll-ready-card__amount {
    min-width: 140px;
    text-align: right;
  }
  .payroll-ready-card__actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .payroll-ready-card__actions form {
    margin: 0;
  }
  .payroll-ready-empty {
    text-align: center;
    padding: 42px 20px;
    background: #ffffff;
    border: 1px dashed #e5ecf5;
    border-radius: 12px;
    color: #64748b;
  }

  @media (max-width: 767.98px) {
    .payroll-ready-card__amount {
      text-align: left;
      width: 100%;
    }
    .payroll-ready-card__actions {
      width: 100%;
    }
    .payroll-ready-card__actions .btn {
      flex: 1 1 auto;
    }
    .payroll-month-picker__panel {
      right: auto;
      left: 0;
    }
  }

  [data-bs-theme="dark"] .payroll-month-picker__trigger,
  [data-bs-theme="dark"] .payroll-month-picker__clear,
  [data-bs-theme="dark"] .payroll-month-picker__panel,
  [data-bs-theme="dark"] .payroll-ready-card,
  [data-bs-theme="dark"] .payroll-ready-empty {
    background: #1e2129;
    border-color: rgba(255,255,255,0.08);
    color: #e2e8f0;
  }
  [data-bs-theme="dark"] .payroll-month-picker__nav,
  [data-bs-theme="dark"] .payroll-month-picker__month {
    background: rgba(255,255,255,0.04);
    color: #cbd5e1;
  }
  [data-bs-theme="dark"] .payroll-month-picker__month.is-active {
    background: #3f6fd9;
    color: #ffffff;
  }
  [data-bs-theme="dark"] .payroll-ready-card__avatar {
    background: rgba(63, 111, 217, 0.18);
    color: #93b4ff;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  (function () {
    const root = document.querySelector('[data-payroll-month-picker]');
    if (!root) return;

    const toggle = root.querySelector('[data-picker-toggle]');
    const panel = root.querySelector('[data-picker-panel]');
    const yearEl = root.querySelector('[data-picker-year]');
    const grid = root.querySelector('[data-picker-grid]');
    const prev = root.querySelector('[data-picker-prev]');
    const next = root.querySelector('[data-picker-next]');
    const selected = <?php echo json_encode($readyMonth ?? null, 15, 512) ?>;
    const baseParams = <?php echo json_encode($queryBase, 15, 512) ?>;
    const indexUrl = <?php echo json_encode(route('driver-payroll.index'), 15, 512) ?>;

    let year = Number(yearEl.textContent);

    function buildUrl(monthValue) {
      const params = new URLSearchParams();
      Object.keys(baseParams || {}).forEach((key) => {
        const value = baseParams[key];
        if (value === null || value === undefined || value === '') return;
        params.set(key, value);
      });
      params.set('ready_month', monthValue);
      const query = params.toString();
      return query ? `${indexUrl}?${query}` : `${indexUrl}?ready_month=${monthValue}`;
    }

    function renderMonths() {
      yearEl.textContent = String(year);
      const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      grid.innerHTML = labels.map((label, index) => {
        const month = String(index + 1).padStart(2, '0');
        const value = `${year}-${month}`;
        const active = selected === value ? ' is-active' : '';
        return `<a href="${buildUrl(value)}" class="payroll-month-picker__month${active}" data-month="${index + 1}">${label}</a>`;
      }).join('');
    }

    function setOpen(open) {
      panel.hidden = !open;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open && window.lucide) {
        window.lucide.createIcons();
      }
    }

    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      setOpen(panel.hidden);
    });

    prev.addEventListener('click', (e) => {
      e.stopPropagation();
      year -= 1;
      renderMonths();
    });

    next.addEventListener('click', (e) => {
      e.stopPropagation();
      year += 1;
      renderMonths();
    });

    document.addEventListener('click', (e) => {
      if (!root.contains(e.target)) setOpen(false);
    });
  })();
</script>
<script>
  // "New driver bill": driver -> shift -> billing period, loaded from what is actually billable.
  (function () {
    const driverSel = document.getElementById('ndbDriver');
    const shiftSel = document.getElementById('ndbShift');
    const periodSel = document.getElementById('ndbPeriod');
    const submit = document.getElementById('ndbSubmit');
    const estimate = document.getElementById('ndbEstimate');
    const estimateAmount = document.getElementById('ndbEstimateAmount');
    const emptyNote = document.getElementById('ndbEmpty');
    const reqId = document.getElementById('ndbRequestId');
    const pStart = document.getElementById('ndbPeriodStart');
    const pEnd = document.getElementById('ndbPeriodEnd');
    const optionsUrl = <?php echo json_encode(route('driver-payroll.bill-options', ':id'), 512) ?>;
    let shifts = [];

    if (!driverSel) return;

    function reset(select, placeholder, disabled) {
      select.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = placeholder;
      select.appendChild(opt);
      select.disabled = disabled;
    }

    function clearChoice() {
      reqId.value = pStart.value = pEnd.value = '';
      submit.disabled = true;
      estimate.classList.add('d-none');
    }

    function money(value) {
      return 'PKR ' + Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    driverSel.addEventListener('change', function () {
      clearChoice();
      emptyNote.classList.add('d-none');
      reset(periodSel, 'Select a shift first', true);
      shifts = [];

      if (!driverSel.value) {
        reset(shiftSel, 'Select a driver first', true);
        return;
      }

      reset(shiftSel, 'Loading shifts…', true);
      fetch(optionsUrl.replace(':id', driverSel.value), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then((res) => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
        .then((data) => {
          shifts = data.shifts || [];
          if (!shifts.length) {
            reset(shiftSel, 'No shifts ready for billing', true);
            emptyNote.textContent = (data.driver || 'This driver') + ' has no completed, paid shift months waiting for a bill.';
            emptyNote.classList.remove('d-none');
            return;
          }
          reset(shiftSel, 'Select shift', false);
          shifts.forEach((shift, index) => {
            const opt = document.createElement('option');
            opt.value = String(index);
            opt.textContent = shift.title;
            shiftSel.appendChild(opt);
          });
        })
        .catch(() => {
          reset(shiftSel, 'Could not load shifts', true);
          emptyNote.textContent = 'Shifts could not be loaded. Please try again.';
          emptyNote.classList.remove('d-none');
        });
    });

    shiftSel.addEventListener('change', function () {
      clearChoice();
      reset(periodSel, 'Select a shift first', true);
      const shift = shifts[Number(shiftSel.value)];
      if (shiftSel.value === '' || !shift) return;

      reset(periodSel, 'Select billing period', false);
      shift.periods.forEach((period, index) => {
        const opt = document.createElement('option');
        opt.value = String(index);
        opt.textContent = period.label;
        periodSel.appendChild(opt);
      });
      if (shift.periods.length === 1) {
        periodSel.value = '0';
        periodSel.dispatchEvent(new Event('change'));
      }
    });

    periodSel.addEventListener('change', function () {
      clearChoice();
      const shift = shifts[Number(shiftSel.value)];
      const period = shift && shift.periods[Number(periodSel.value)];
      if (periodSel.value === '' || !period) return;

      reqId.value = shift.id;
      pStart.value = period.start;
      pEnd.value = period.end;
      estimateAmount.textContent = money(period.amount);
      estimate.classList.remove('d-none');
      submit.disabled = false;
    });

    document.getElementById('newDriverBillModal').addEventListener('hidden.bs.modal', function () {
      driverSel.value = '';
      driverSel.dispatchEvent(new Event('change'));
    });
  })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/driver-payroll/index.blade.php ENDPATH**/ ?>