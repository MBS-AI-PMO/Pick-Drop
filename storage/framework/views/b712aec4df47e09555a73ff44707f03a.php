<?php
  $requester = $requestItem->parent;
  $isSelf = $requestItem->type === 'self';
  $days = collect($requestItem->days ?? [])->map(fn ($day) => ucfirst($day))->implode(', ');
?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Request #<?php echo e($requestItem->id); ?></h4>
    <p class="text-secondary mb-0">
      <?php echo e($requestItem->typeLabel()); ?> pickup · <?php echo e($requester?->name ?? 'Unknown user'); ?>

    </p>
  </div>
  <div class="d-flex flex-wrap gap-2 align-items-center">
    <span class="badge rounded-pill px-3 py-2" style="<?php echo e($requestItem->statusBadgeStyle()); ?>">
      <?php echo e($requestItem->statusLabel()); ?>

    </span>
    <a href="<?php echo e(route('pickup-requests.index')); ?>" class="btn btn-outline-secondary">Back to list</a>
    <form action="<?php echo e(route('pickup-requests.destroy', $requestItem)); ?>" method="POST" onsubmit="confirmDelete(event, this)">
      <?php echo csrf_field(); ?>
      <?php echo method_field('DELETE'); ?>
      <button type="submit" class="btn btn-outline-danger">Delete</button>
    </form>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Requested by</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">Name</label>
            <div class="fw-semibold"><?php echo e($requester?->name ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Account type</label>
            <div>
              <?php if($isSelf): ?>
                <span class="badge bg-info text-dark">Self</span>
              <?php else: ?>
                <span class="badge bg-primary">Parent</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Email</label>
            <div class="fw-semibold"><?php echo e($requester?->email ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Phone</label>
            <div class="fw-semibold"><?php echo e($requester?->phone ?? '—'); ?></div>
          </div>
          <?php if($requestItem->student): ?>
            <div class="col-md-6">
              <label class="text-muted small">Child</label>
              <div class="fw-semibold"><?php echo e($requestItem->student->name); ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Trip details</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">City</label>
            <div class="fw-semibold"><?php echo e($requestItem->city?->name ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Days</label>
            <div class="fw-semibold"><?php echo e($days ?: '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Pickup area</label>
            <div class="fw-semibold"><?php echo e($requestItem->area?->name ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Drop area</label>
            <div class="fw-semibold"><?php echo e($requestItem->dropArea?->name ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Pickup point</label>
            <div class="fw-semibold"><?php echo e($requestItem->pickup_point ?: '—'); ?></div>
            <small class="text-muted"><?php echo e($requestItem->pickup_time ? substr((string) $requestItem->pickup_time, 0, 5) : '—'); ?></small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Drop point</label>
            <div class="fw-semibold"><?php echo e($requestItem->drop_point ?: '—'); ?></div>
            <small class="text-muted"><?php echo e($requestItem->drop_time ? substr((string) $requestItem->drop_time, 0, 5) : '—'); ?></small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Scheduled date</label>
            <div class="fw-semibold"><?php echo e($requestItem->scheduled_date?->format('d M Y') ?: '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Submitted</label>
            <div class="fw-semibold"><?php echo e($requestItem->created_at?->format('d M Y, h:i A')); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Shift duration</label>
            <div class="fw-semibold"><?php echo e((int) ($requestItem->duration_months ?: 1)); ?> month<?php echo e((int) ($requestItem->duration_months ?: 1) === 1 ? '' : 's'); ?></div>
            <small class="text-muted"><?php echo e($requestItem->shift_start_date?->format('d M Y') ?: '—'); ?> → <?php echo e($requestItem->shift_end_date?->format('d M Y') ?: '—'); ?></small>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Trip mode</label>
            <div class="fw-semibold"><?php echo e($requestItem->tripModeLabel()); ?></div>
            <small class="text-muted">
              <?php if($requestItem->tripMode() === \App\Models\PickupRequest::TRIP_ROUND): ?>
                Morning A to B, return B to A (pick up from the drop point, drop at the morning pickup).
              <?php else: ?>
                One direction only. No return.
              <?php endif; ?>
            </small>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Pickup &amp; drop points</h6>
      </div>
      <div class="card-body">
        <?php $journey = $requestItem->journeyApiArray(); ?>
        <p class="text-secondary small mb-3"><?php echo e($journey['rule'] ?? 'Passenger is dropped back at the same place they were picked up.'); ?></p>
        <?php $__empty_1 = true; $__currentLoopData = ($journey['stops'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="d-flex justify-content-between align-items-start mb-2 pb-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
            <div>
              <div class="fw-semibold"><?php echo e($stop['sequence'] ?? $loop->iteration); ?>. <?php echo e($stop['action'] ?? $stop['point']); ?></div>
              <small class="text-muted"><?php echo e(ucfirst($stop['leg'] ?? 'outbound')); ?> · <?php echo e($stop['point'] ?? '—'); ?> · <?php echo e($stop['time'] ?? '—'); ?></small>
            </div>
            <span class="badge text-bg-light"><?php echo e(($stop['virtual'] ?? false) ? 'Return' : ucfirst($stop['type'] ?? 'stop')); ?></span>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <p class="text-muted mb-0">No stops recorded yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Driver & vehicle</h6>
      </div>
      <div class="card-body">
        <?php if($requestItem->driver): ?>
          <div class="mb-3">
            <label class="text-muted small">Driver</label>
            <div class="fw-semibold"><?php echo e($requestItem->driver->name); ?></div>
            <small class="text-muted"><?php echo e($requestItem->driver->email); ?></small>
          </div>
          <div>
            <label class="text-muted small">Vehicle</label>
            <div class="fw-semibold"><?php echo e($requestItem->vehicle?->name ?? '—'); ?></div>
            <small class="text-muted"><?php echo e($requestItem->vehicle?->license_plate ?? $requestItem->vehicle?->category?->name ?? '—'); ?></small>
          </div>
        <?php else: ?>
          <p class="text-secondary mb-0">Waiting for a driver to accept this request.</p>
        <?php endif; ?>
        <?php if(($liveDriver ?? null) && $requestItem->driver && (int) $liveDriver->id !== (int) $requestItem->driver_id): ?>
          <div class="mt-3 pt-3 border-top">
            <label class="text-muted small">Today’s cover driver</label>
            <div class="fw-semibold"><?php echo e($liveDriver->name); ?></div>
            <small class="text-muted"><?php echo e($liveDriver->phone ?: $liveDriver->email); ?></small>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if($canAssignCover ?? false): ?>
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Alternative driver</h6></div>
      <div class="card-body">
        <p class="text-secondary small">If the assigned driver cannot come or the vehicle breaks down, send a nearby driver for that day. If nobody covers, that day’s amount is refunded to the parent wallet after 8pm.</p>
        <?php if(($coverDrivers ?? collect())->isNotEmpty()): ?>
          <form method="POST" action="<?php echo e(route('pickup-requests.cover', $requestItem)); ?>">
            <?php echo csrf_field(); ?>
            <label class="text-muted small">Date</label>
            <input type="date" name="date" class="form-control mb-2" value="<?php echo e(now()->toDateString()); ?>" required>
            <label class="text-muted small">Reason</label>
            <select name="reason" class="form-select mb-2">
              <option value="unavailable">Driver unavailable</option>
              <option value="breakdown">Vehicle breakdown</option>
              <option value="absent">Absent</option>
            </select>
            <label class="text-muted small">Replacement driver</label>
            <select name="driver_id" class="form-select mb-2" required>
              <option value="">Select driver</option>
              <?php $__currentLoopData = $coverDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?> · <?php echo e($driver->phone ?: $driver->email); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="btn btn-sm btn-dark" type="submit">Assign for this day</button>
          </form>
        <?php else: ?>
          <p class="text-muted mb-0">No other eligible driver in this city/area yet.</p>
        <?php endif; ?>
        <?php if($requestItem->replacements->isNotEmpty()): ?>
          <div class="mt-3 pt-3 border-top">
            <label class="text-muted small d-block mb-2">Cover history</label>
            <?php $__currentLoopData = $requestItem->replacements->sortByDesc('date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $replacement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="small mb-2">
                <div class="fw-semibold"><?php echo e($replacement->date?->format('d M Y')); ?> · <?php echo e(ucfirst($replacement->reason)); ?></div>
                <div class="text-secondary">
                  <?php echo e(ucfirst($replacement->status)); ?>

                  <?php if($replacement->replacementDriver): ?>
                    · <?php echo e($replacement->replacementDriver->name); ?>

                  <?php else: ?>
                    · waiting for a driver
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Customer advance</h6>
        <span class="badge rounded-pill px-3 py-1" style="<?php echo e($requestItem->paymentStatusBadgeStyle()); ?>">
          <?php echo e($requestItem->paymentStatusLabel()); ?>

        </span>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <label class="text-muted small">Amount</label>
          <div class="fw-semibold">
            <?php if($requestItem->latestInvoice): ?>
              <?php echo e($requestItem->latestInvoice->formattedTotal()); ?>

            <?php elseif($requestItem->estimated_amount): ?>
              PKR <?php echo e(number_format((float) $requestItem->estimated_amount, 2)); ?>

            <?php else: ?>
              —
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-2">
          <label class="text-muted small">Distance / trips</label>
          <div class="fw-semibold"><?php echo e($requestItem->distance_km ? number_format((float) $requestItem->distance_km, 2) . ' km' : '—'); ?> · <?php echo e($requestItem->trip_count ?: '—'); ?> trips</div>
        </div>
        <?php if($requestItem->latestInvoice): ?>
          <a href="<?php echo e(route('payments.show', $requestItem->latestInvoice)); ?>" class="btn btn-sm btn-outline-secondary mt-1">Open invoice</a>
        <?php elseif(!$requestItem->driver): ?>
          <p class="text-secondary fs-12px mb-0 mt-2">Invoice and payment methods appear after a driver accepts.</p>
        <?php endif; ?>
        <?php if($requestItem->needsPayment()): ?>
          <p class="text-secondary fs-12px mb-0 mt-2">Service stays locked until this advance payment is confirmed.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Driver payment</h6>
        <?php $latestBill = $requestItem->payrollBills()->latest('id')->first(); ?>
        <?php if($latestBill): ?>
          <span class="badge rounded-pill px-3 py-1" style="<?php echo e($latestBill->statusBadgeStyle()); ?>"><?php echo e($latestBill->statusLabel()); ?></span>
        <?php else: ?>
          <span class="badge rounded-pill px-3 py-1" style="background:#f3f4f6;color:#6b7280;">No bill yet</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <label class="text-muted small">Monthly rate</label>
          <div class="fw-semibold">PKR <?php echo e(number_format((float) ($requestItem->driver_monthly_rate ?? 0), 2)); ?></div>
        </div>
        <div class="mb-2">
          <label class="text-muted small">Shift period</label>
          <div class="fw-semibold fs-13px"><?php echo e($requestItem->shift_start_date?->format('d M Y') ?: '—'); ?> – <?php echo e($requestItem->shift_end_date?->format('d M Y') ?: '—'); ?></div>
        </div>
        <p class="text-secondary fs-12px mb-3">Bills are calculated from attendance at month end. Manage payouts in <a href="<?php echo e(route('driver-payroll.index')); ?>">Driver Payments</a>.</p>
        <?php if($latestBill): ?>
          <a href="<?php echo e(route('driver-payroll.show', $latestBill)); ?>" class="btn btn-sm btn-outline-secondary">View payment bill</a>
        <?php elseif($requestItem->driver): ?>
          <p class="text-secondary fs-12px mb-0">The driver can submit a payment request from the app once a billing period is complete.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php if($requestItem->status === 'pending' && empty($requestItem->driver_id)): ?>
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Assign driver</h6></div>
      <div class="card-body">
        <p class="text-secondary small">If no driver accepts within 20 minutes, the system auto-assigns an eligible driver. You can also assign now.</p>
        <?php if(($eligibleDrivers ?? collect())->isNotEmpty()): ?>
          <form method="POST" action="<?php echo e(route('pickup-requests.assign', $requestItem)); ?>">
            <?php echo csrf_field(); ?>
            <select name="driver_id" class="form-select mb-2" required>
              <option value="">Select driver</option>
              <?php $__currentLoopData = $eligibleDrivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($driver->id); ?>"><?php echo e($driver->name); ?> · <?php echo e($driver->phone ?: $driver->email); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button class="btn btn-sm btn-dark" type="submit">Assign &amp; create invoice</button>
          </form>
        <?php else: ?>
          <p class="text-muted mb-0">No eligible active driver in this city/area yet.</p>
        <?php endif; ?>
        <?php if($requestItem->match_expires_at): ?>
          <small class="text-muted d-block mt-2">Auto-assign after <?php echo e($requestItem->match_expires_at->format('d M Y, h:i A')); ?></small>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if($liveDriver ?? $requestItem->driver): ?>
    <?php $mapDriver = $liveDriver ?? $requestItem->driver; ?>
    <div class="card mb-3">
      <div class="card-header"><h6 class="mb-0">Live tracking</h6></div>
      <div class="card-body">
        <?php if($mapDriver->last_lat && $mapDriver->last_lng): ?>
          <div class="fw-semibold mb-1"><?php echo e($mapDriver->last_lat); ?>, <?php echo e($mapDriver->last_lng); ?></div>
          <small class="text-muted d-block mb-2">Updated <?php echo e($mapDriver->last_location_at?->diffForHumans() ?: '—'); ?> · <?php echo e($mapDriver->last_ride_status ?: 'no status'); ?></small>
          <a class="btn btn-sm btn-outline-dark" target="_blank"
             href="https://maps.google.com/?q=<?php echo e($mapDriver->last_lat); ?>,<?php echo e($mapDriver->last_lng); ?>">Open map</a>
        <?php else: ?>
          <p class="text-muted mb-0">Driver has not shared a live location yet.</p>
        <?php endif; ?>
        <?php if($mapDriver->last_lat): ?>
          <div id="live-map" class="mt-3 rounded" style="height:220px;"></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Timeline</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="text-muted small">Created</label>
          <div class="fw-semibold"><?php echo e($requestItem->created_at?->format('d M Y, h:i A')); ?></div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Last updated</label>
          <div class="fw-semibold"><?php echo e($requestItem->updated_at?->format('d M Y, h:i A')); ?></div>
        </div>
        <?php if($requestItem->cancelled_at): ?>
          <div class="mb-3">
            <label class="text-muted small">Cancelled</label>
            <div class="fw-semibold"><?php echo e($requestItem->cancelled_at->format('d M Y, h:i A')); ?></div>
          </div>
        <?php endif; ?>
        <?php if($requestItem->completed_at): ?>
          <div>
            <label class="text-muted small">Completed</label>
            <div class="fw-semibold"><?php echo e($requestItem->completed_at->format('d M Y, h:i A')); ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $mapDriver = $liveDriver ?? $requestItem->driver; ?>
<?php if($mapDriver?->last_lat): ?>
<?php $__env->startPush('custom-scripts'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  (function () {
    var lat = <?php echo e($mapDriver->last_lat); ?>;
    var lng = <?php echo e($mapDriver->last_lng); ?>;
    var map = L.map('live-map').setView([lat, lng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(<?php echo json_encode($mapDriver->name ?: 'Driver', 15, 512) ?>);
    <?php if($requestItem->pickup_lat): ?>
      L.marker([<?php echo e($requestItem->pickup_lat); ?>, <?php echo e($requestItem->pickup_lng); ?>]).addTo(map).bindPopup('Pickup');
    <?php endif; ?>
    <?php if($requestItem->drop_lat): ?>
      L.marker([<?php echo e($requestItem->drop_lat); ?>, <?php echo e($requestItem->drop_lng); ?>]).addTo(map).bindPopup('Drop');
    <?php endif; ?>
  })();
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/pickup-requests/show.blade.php ENDPATH**/ ?>